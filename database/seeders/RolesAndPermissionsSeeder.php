<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = ['users', 'patients', 'dentists', 'treatments', 'appointments', 'clinical-records', 'payments'];
        $actions   = ['view', 'create', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::findOrCreate("$resource.$action", 'web');
            }
        }

        // Administrador: todo
        Role::findOrCreate('admin', 'web')->syncPermissions(Permission::all());

        // Recepción: pacientes, citas y cobros; solo consulta de odontólogos y tratamientos
        Role::findOrCreate('receptionist', 'web')->syncPermissions([
            'patients.view', 'patients.create', 'patients.update', 'patients.delete',
            'dentists.view', 'treatments.view',
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
            'payments.view', 'payments.create', 'payments.update',
        ]);

        // Odontólogo: sus citas, pacientes (alta y edición), historias clínicas
        Role::findOrCreate('dentist', 'web')->syncPermissions([
            'patients.view', 'patients.create', 'patients.update',
            'dentists.view', 'treatments.view',
            'appointments.view', 'appointments.update',
            'clinical-records.view', 'clinical-records.create', 'clinical-records.update',
        ]);

        // Usuario administrador inicial
        $password = env('SEED_ADMIN_PASSWORD');
        $generated = false;
        if (! $password) {
            $password  = Str::password(16);
            $generated = true;
        }

        $admin = User::firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@clinica.test')],
            ['name' => 'Administrador', 'password' => $password, 'is_active' => true],
        );
        $admin->syncRoles(['admin']);

        if ($admin->wasRecentlyCreated && $generated) {
            $this->command?->warn("Admin creado: {$admin->email} / contraseña: {$password}  (guárdela y cámbiela)");
        }
    }
}
