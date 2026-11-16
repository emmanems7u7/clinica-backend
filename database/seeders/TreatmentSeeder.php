<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Database\Seeders\Concerns\SeedsSafely;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    use SeedsSafely;

    // [código, nombre, precio (Bs), duración (min), descripción]
    private const CATALOG = [
        ['CON-001', 'Consulta y diagnóstico', 80, 30, 'Evaluación clínica completa y plan de tratamiento.'],
        ['LIM-001', 'Limpieza dental (profilaxis)', 150, 45, 'Remoción de placa y sarro con ultrasonido y pulido.'],
        ['OBT-001', 'Obturación con resina', 200, 45, 'Restauración de caries con resina fotocurable.'],
        ['EXT-001', 'Extracción simple', 180, 30, 'Extracción de pieza dental con anestesia local.'],
        ['EXM-001', 'Extracción de muela del juicio', 450, 60, 'Cirugía de tercer molar, incluye controles.'],
        ['END-001', 'Endodoncia (tratamiento de conducto)', 650, 60, 'Tratamiento de conducto en una sesión.'],
        ['BLA-001', 'Blanqueamiento dental', 800, 60, 'Blanqueamiento en consultorio con lámpara LED.'],
        ['COR-001', 'Corona de porcelana', 1200, 60, 'Corona libre de metal, incluye prueba y cementado.'],
        ['ORT-001', 'Control de ortodoncia', 250, 30, 'Ajuste mensual de aparatología fija.'],
        ['BRK-001', 'Instalación de brackets', 2500, 90, 'Colocación de aparatología ortodóntica fija.'],
        ['SEL-001', 'Sellantes de fosas y fisuras', 100, 30, 'Protección preventiva para molares.'],
        ['FLU-001', 'Aplicación de flúor', 90, 20, 'Fluorización tópica preventiva.'],
        ['RXP-001', 'Radiografía periapical', 40, 15, 'Radiografía digital de una pieza.'],
        ['IMP-001', 'Implante dental', 4500, 90, 'Colocación de implante de titanio (sin corona).'],
        ['PRO-001', 'Prótesis removible', 1800, 60, 'Prótesis parcial o total removible.'],
        ['CAR-001', 'Carilla de resina', 500, 60, 'Carilla estética directa en resina.'],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as [$code, $name, $price, $min, $desc]) {
            if (Treatment::where('code', $code)->exists()) {
                continue;
            }
            $this->make(Treatment::class, [
                'code' => $code, 'name' => $name, 'price' => $price,
                'duration_minutes' => $min, 'description' => $desc, 'is_active' => true,
            ]);
        }
        $this->command?->info('Tratamientos: ' . Treatment::count());
    }
}
