<?php

namespace Database\Seeders;

use App\Models\Dentist;
use App\Models\User;
use Database\Seeders\Concerns\SeedsSafely;
use Database\Seeders\Support\BoliviaData as B;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Odontólogos y recepcionistas de ejemplo. Contraseña común: SEED_DEMO_PASSWORD. */
class StaffSeeder extends Seeder
{
    use SeedsSafely;

    private const DENTISTS = [
        ['Rodrigo', 'Salazar Arce', 'Odontología general'],
        ['Carla', 'Mendoza Rivera', 'Ortodoncia'],
        ['Fernando', 'Quispe Aliaga', 'Endodoncia'],
        ['Verónica', 'Paredes Montes', 'Cirugía maxilofacial'],
        ['Daniela', 'Flores Zeballos', 'Odontopediatría'],
    ];
    private const RECEPTIONISTS = [['Silvia', 'Poma Nina'], ['Marco', 'Ticona Yujra']];

    public function run(): void
    {
        $pass = Hash::make(env('SEED_DEMO_PASSWORD') ?: 'Clinica#2026Demo');

        foreach (self::RECEPTIONISTS as [$first, $last]) {
            $user = $this->user("$first $last", 'recepcion.' . B::slug($first), $pass);
            $this->assignRole($user, ['receptionist', 'recepcionista']);
        }

        foreach (self::DENTISTS as $i => [$first, $last, $specialty]) {
            $user = $this->user("Dr(a). $first $last", 'dr.' . B::slug($first . ' ' . explode(' ', $last)[0]), $pass);
            $this->assignRole($user, ['dentist', 'odontologo', 'odontólogo']);
            if (Dentist::where('user_id', $user->id)->exists()) {
                continue;
            }
            $this->make(Dentist::class, [
                'user_id' => $user->id, 'license_number' => sprintf('MAT-%d-%03d', 2015 + $i, 120 + $i * 7),
                'specialty' => $specialty, 'phone' => B::phone(), 'is_active' => true,
            ]);
        }
        $this->command?->info('Odontólogos: ' . Dentist::count() . ' · Usuarios: ' . User::count());
    }

    private function user(string $name, string $local, string $hash): User
    {
        $email = "$local@clinica.test";

        return User::where('email', $email)->first() ?? $this->make(User::class, [
            'name' => $name, 'email' => $email, 'password' => $hash, 'phone' => B::phone(),
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }
}
