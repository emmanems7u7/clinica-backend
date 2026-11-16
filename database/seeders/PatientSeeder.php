<?php

namespace Database\Seeders;

use App\Models\Patient;
use Database\Seeders\Concerns\SeedsSafely;
use Database\Seeders\Support\BoliviaData as B;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    use SeedsSafely;

    private const ALLERGIES = ['Penicilina', 'Látex', 'Ibuprofeno', 'Lidocaína (reacción leve)', 'Polen y polvo', 'Sulfas'];
    private const HISTORY = ['Hipertensión controlada', 'Diabetes tipo 2 controlada', 'Asma leve', 'Sin antecedentes relevantes', 'Bruxismo', 'Gingivitis recurrente', 'Apendicectomía (2018)', 'Tratamiento de ortodoncia previo'];
    private const NOTES = ['Prefiere citas por la tarde.', 'Paciente con ansiedad dental.', 'Se recomienda recordatorio por WhatsApp.', 'Viene acompañado de un familiar.'];

    public function run(): void
    {
        $n = (int) (env('SEED_PATIENTS') ?: 60);

        for ($i = 1; $i <= $n; $i++) {
            $doc = (4000000 + $i * 1237) . ' ' . B::pick(B::CI_EXT);
            if (Patient::where('document_number', $doc)->exists()) {
                continue;
            }
            [$first, $last, $gender] = B::person();
            $age = random_int(1, 100) <= 15 ? random_int(4, 17) : random_int(18, 75);
            $emerg = B::person();

            $this->make(Patient::class, [
                'first_name' => $first, 'last_name' => $last, 'full_name' => "$first $last",
                'document_number' => $doc, 'gender' => $gender,
                'birth_date' => now()->subYears($age)->subDays(random_int(0, 364))->toDateString(),
                'phone' => B::phone(),
                'email' => B::slug($first) . '.' . B::slug(explode(' ', $last)[0]) . $i . '@correo.test',
                'address' => B::address(),
                'emergency_contact_name' => "$emerg[0] $emerg[1]", 'emergency_contact_phone' => B::phone(),
                'allergies' => random_int(1, 100) <= 25 ? B::pick(self::ALLERGIES) : null,
                'medical_history' => random_int(1, 100) <= 60 ? B::pick(self::HISTORY) : null,
                'notes' => random_int(1, 100) <= 20 ? B::pick(self::NOTES) : null,
            ]);
        }
        $this->command?->info('Pacientes: ' . Patient::count());
    }
}
