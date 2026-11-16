<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos de ejemplo para la clínica.
 *   php artisan db:seed --class=DemoDataSeeder
 * Variables opcionales en .env: SEED_PATIENTS (60), SEED_APPOINTMENTS (150),
 * SEED_DEMO_PASSWORD (Clinica#2026Demo), SEED_FORCE=true (permite correrlo en producción).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && !env('SEED_FORCE')) {
            $this->command?->error('Entorno de producción: no se cargan datos de ejemplo (SEED_FORCE=true para forzar).');

            return;
        }

        DB::transaction(fn () => $this->call([
            TreatmentSeeder::class,
            StaffSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
            ClinicalRecordSeeder::class,
            PaymentSeeder::class,
        ]));
    }
}
