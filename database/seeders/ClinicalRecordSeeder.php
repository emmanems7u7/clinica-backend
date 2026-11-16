<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClinicalRecord;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsSafely;
use Database\Seeders\Support\BoliviaData as B;
use Illuminate\Database\Seeder;

/** Una historia clínica por cada cita completada (aprox. 85%). */
class ClinicalRecordSeeder extends Seeder
{
    use SeedsSafely;

    // prefijo del código de tratamiento => [diagnóstico, procedimiento, receta]. {pz} = pieza dental (FDI)
    private const TEXTS = [
        'CON' => ['Control de rutina sin hallazgos agudos.', 'Examen clínico e intraoral, revisión de tejidos blandos y oclusión.', null],
        'LIM' => ['Placa y cálculo supragingival. Gingivitis leve.', 'Profilaxis con ultrasonido, pulido y refuerzo de técnica de cepillado.', 'Enjuague de clorhexidina 0.12% cada 12 h por 7 días.'],
        'OBT' => ['Caries dentinaria en pieza {pz}.', 'Anestesia local, remoción de tejido cariado y obturación con resina fotocurable en pieza {pz}.', 'Ibuprofeno 400 mg cada 8 h por 2 días si hay molestia.'],
        'EXT' => ['Pieza {pz} con destrucción coronaria no restaurable.', 'Anestesia infiltrativa, extracción simple de pieza {pz} y hemostasia con gasa.', 'Amoxicilina 500 mg cada 8 h por 5 días. Ibuprofeno 400 mg cada 8 h por 3 días.'],
        'EXM' => ['Tercer molar {pz} incluido con pericoronaritis.', 'Colgajo, osteotomía y extracción de la pieza {pz}. Sutura simple.', 'Amoxicilina 875 mg cada 12 h por 7 días. Ketorolaco 10 mg cada 8 h por 3 días. Retiro de puntos en 7 días.'],
        'END' => ['Pulpitis irreversible en pieza {pz}.', 'Apertura cameral, instrumentación y obturación de conductos de la pieza {pz}.', 'Ibuprofeno 400 mg cada 8 h por 3 días.'],
        'BLA' => ['Pigmentación extrínseca generalizada.', 'Blanqueamiento en consultorio con gel de peróxido y lámpara LED.', 'Evitar café, té y tabaco por 48 h.'],
        'COR' => ['Pieza {pz} con restauración extensa fracturada.', 'Tallado, impresión y colocación de corona provisional en pieza {pz}.', null],
        'ORT' => ['Maloclusión en tratamiento ortodóntico.', 'Cambio de arco y ajuste de aparatología fija.', 'Cera ortodóntica según necesidad.'],
        'BRK' => ['Apiñamiento dental moderado.', 'Profilaxis, grabado y cementado de brackets superiores e inferiores.', 'Cera ortodóntica. Dieta blanda por 3 días.'],
        'SEL' => ['Fosas y fisuras profundas en molares.', 'Aislamiento, grabado y aplicación de sellantes en molares.', null],
        'FLU' => ['Riesgo cariogénico moderado.', 'Aplicación tópica de barniz de flúor.', 'No ingerir alimentos por 30 minutos.'],
        'RXP' => ['Evaluación radiográfica de pieza {pz}.', 'Toma de radiografía periapical digital de la pieza {pz}.', null],
        'IMP' => ['Edentulismo parcial en zona de pieza {pz}.', 'Colocación de implante de titanio en zona de la pieza {pz}.', 'Amoxicilina 500 mg cada 8 h por 7 días. Dexametasona 4 mg cada 12 h por 2 días.'],
        'PRO' => ['Edentulismo parcial con pérdida de función masticatoria.', 'Toma de impresiones y prueba de estructura de prótesis removible.', null],
        'CAR' => ['Fractura de borde incisal en pieza {pz}.', 'Carilla de resina directa en pieza {pz}, pulido y ajuste oclusal.', null],
    ];

    public function run(): void
    {
        if (ClinicalRecord::exists()) {
            $this->command?->warn('Ya existen historias clínicas: se omite.');

            return;
        }

        $pieces = array_merge(range(11, 18), range(21, 28), range(31, 38), range(41, 48));
        $n = 0;

        Appointment::where('status', 'completed')->with('treatments')->get()->each(function ($a) use ($pieces, &$n) {
            if (random_int(1, 100) > 85) {
                return;
            }
            $prefix = strtok((string) ($a->treatments->first()->code ?? 'CON'), '-');
            [$diag, $proc, $rx] = self::TEXTS[$prefix] ?? self::TEXTS['CON'];
            $pz = (string) B::pick($pieces);
            $end = Carbon::parse($a->end_at);

            $this->make(ClinicalRecord::class, [
                'patient_id' => $a->patient_id, 'dentist_id' => $a->dentist_id, 'appointment_id' => $a->id,
                'diagnosis' => str_replace('{pz}', $pz, $diag),
                'procedure_notes' => str_replace('{pz}', $pz, $proc),
                'prescription' => $rx,
                'created_at' => $end, 'updated_at' => $end,
            ]);
            $n++;
        });
        $this->command?->info("Historias clínicas creadas: $n");
    }
}
