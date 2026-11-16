<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Payment;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsSafely;
use Illuminate\Database\Seeder;

/** Un pago por cada cita completada, con el total de sus tratamientos (en bolivianos). */
class PaymentSeeder extends Seeder
{
    use SeedsSafely;

    public function run(): void
    {
        if (Payment::exists()) {
            $this->command?->warn('Ya existen pagos: se omite.');

            return;
        }

        $n = 0;
        Appointment::where('status', 'completed')->with('treatments')->get()->each(function ($a) use (&$n) {
            $amount = (float) $a->treatments->sum('price');
            if ($amount <= 0) {
                return;
            }
            $r = random_int(1, 100);
            $status = $r <= 85 ? 'paid' : ($r <= 93 ? 'pending' : 'refunded');
            $m = random_int(1, 100);
            $method = $m <= 40 ? 'cash' : ($m <= 70 ? 'qr' : ($m <= 90 ? 'card' : 'transfer'));
            $paidAt = Carbon::parse($a->end_at)->addMinutes(random_int(0, 30));

            $this->make(Payment::class, [
                'patient_id' => $a->patient_id, 'appointment_id' => $a->id,
                'amount' => $amount, 'currency' => 'BOB', 'method' => $method, 'status' => $status,
                'paid_at' => $status === 'pending' ? null : $paidAt,
                'reference' => $method === 'cash' ? null : strtoupper(substr($method, 0, 3)) . '-' . random_int(100000, 999999),
                'notes' => $status === 'refunded' ? 'Reembolso por ajuste del tratamiento.' : null,
                'created_at' => $paidAt, 'updated_at' => $paidAt,
            ]);
            $n++;
        });
        $this->command?->info("Pagos creados: $n");
    }
}
