<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsSafely;
use Database\Seeders\Support\BoliviaData as B;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Citas de los últimos ~75 días y los próximos 30, de lunes a sábado (08:00–18:00),
 * sin solapar citas del mismo odontólogo.
 */
class AppointmentSeeder extends Seeder
{
    use SeedsSafely;

    private const REASONS = ['Dolor de muela', 'Control de rutina', 'Revisión general', 'Limpieza dental', 'Sensibilidad al frío', 'Sangrado de encías', 'Caries', 'Estética dental', 'Control posoperatorio', 'Consulta por ortodoncia'];
    private const CANCEL = ['El paciente avisó que no puede asistir', 'Reprogramada por el paciente', 'Emergencia familiar', 'Problemas de transporte', 'Cruce con otro compromiso'];

    public function run(): void
    {
        if (Appointment::exists()) {
            $this->command?->warn('Ya existen citas: se omite AppointmentSeeder (use migrate:fresh --seed para reiniciar).');

            return;
        }

        $dentists = Dentist::all();
        $treatments = Treatment::where('is_active', true)->get();
        $patients = Patient::pluck('id')->all();
        if ($dentists->isEmpty() || $treatments->isEmpty() || !$patients) {
            $this->command?->error('Faltan odontólogos, tratamientos o pacientes. Ejecute esos seeders primero.');

            return;
        }

        $total = (int) (env('SEED_APPOINTMENTS') ?: 150);
        $busy = []; // [dentist_id][Y-m-d] => [[inicio, fin], ...]
        $now = now();
        $created = 0;

        for ($i = 0; $i < $total; $i++) {
            $dentist = $dentists->random();
            $picked = $treatments->random(min($treatments->count(), random_int(1, 100) <= 25 ? 2 : 1));
            $minutes = min(120, max(30, (int) $picked->sum('duration_minutes')));

            $day = $now->copy()->startOfDay()->addDays(random_int(-75, 30));
            if ($day->isSunday()) {
                $day->addDay();
            }

            $slot = null;
            for ($try = 0; $try < 15 && !$slot; $try++) {
                $start = $day->copy()->addMinutes(480 + random_int(0, 19) * 30); // 08:00 … 17:30
                $end = $start->copy()->addMinutes($minutes);
                if ($end->gt($day->copy()->setTime(18, 0))) {
                    continue;
                }
                $key = $day->toDateString();
                $clash = collect($busy[$dentist->id][$key] ?? [])->contains(fn ($b) => $start->lt($b[1]) && $end->gt($b[0]));
                if (!$clash) {
                    $busy[$dentist->id][$key][] = [$start, $end];
                    $slot = [$start, $end];
                }
            }
            if (!$slot) {
                continue;
            }
            [$start, $end] = $slot;

            $status = $this->status($start, $end, $now);
            $appointment = $this->make(Appointment::class, [
                'patient_id' => $patients[array_rand($patients)], 'dentist_id' => $dentist->id,
                'start_at' => $start, 'end_at' => $end, 'status' => $status,
                'reason' => B::pick(self::REASONS),
                'notes' => random_int(1, 100) <= 15 ? 'Confirmar por teléfono un día antes.' : null,
                'cancelled_reason' => $status === 'cancelled' ? B::pick(self::CANCEL) : null,
                'created_at' => $start->copy()->subDays(random_int(1, 10)),
            ]);

            foreach ($picked as $t) {
                $this->attach($appointment, $t);
            }
            $created++;
        }
        $this->command?->info("Citas creadas: $created");
    }

    private function status(Carbon $start, Carbon $end, Carbon $now): string
    {
        $r = random_int(1, 100);
        if ($end->lt($now)) {
            return $r <= 74 ? 'completed' : ($r <= 87 ? 'cancelled' : 'no_show');
        }
        if ($start->lte($now)) {
            return 'in_progress';
        }

        return $r <= 6 ? 'cancelled' : ($r <= 58 ? 'confirmed' : 'scheduled');
    }

    /** Adjunta el tratamiento rellenando solo las columnas de la tabla pivote que existan. */
    private function attach(Appointment $appointment, Treatment $t): void
    {
        $rel = $appointment->treatments();
        $pivot = [];
        if ($rel instanceof BelongsToMany) {
            $table = $rel->getTable();
            foreach (['quantity' => 1, 'unit_price' => $t->price, 'price' => $t->price, 'total' => $t->price, 'discount' => 0] as $col => $val) {
                if (Schema::hasColumn($table, $col)) {
                    $pivot[$col] = $val;
                }
            }
        }
        $rel->attach($t->id, $pivot);
    }
}
