<?php

namespace Tests\Feature;

use App\Models\Dentist;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(string $role, string $email): User
    {
        $user = User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'Secreta#12345']);
        $user->assignRole($role);

        return $user;
    }

    private function makeDentist(string $email, string $license): Dentist
    {
        $user = $this->makeUser('dentist', $email);

        return Dentist::create(['user_id' => $user->id, 'license_number' => $license]);
    }

    private function makePatient(string $doc = '1000001'): Patient
    {
        return Patient::create(['first_name' => 'Juan', 'last_name' => 'Pérez', 'document_number' => $doc]);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/patients')->assertStatus(401);
    }

    public function test_login_ok_and_wrong_password(): void
    {
        $this->makeUser('receptionist', 'rec@clinica.test');

        $this->postJson('/api/v1/auth/login', ['email' => 'rec@clinica.test', 'password' => 'Secreta#12345'])
            ->assertOk()->assertJsonStructure(['token', 'token_type', 'expires_at', 'user']);

        $this->postJson('/api/v1/auth/login', ['email' => 'rec@clinica.test', 'password' => 'incorrecta'])
            ->assertStatus(422);
    }

    public function test_permissions_are_enforced_by_role(): void
    {
        Sanctum::actingAs($this->makeUser('receptionist', 'rec@clinica.test'));

        $this->postJson('/api/v1/users', [])->assertStatus(403);
        $this->postJson('/api/v1/patients', ['first_name' => 'Ana', 'last_name' => 'Lima', 'document_number' => '555'])
            ->assertCreated();
    }

    public function test_dentist_cannot_delete_patients(): void
    {
        $patient = $this->makePatient();
        Sanctum::actingAs($this->makeUser('dentist', 'den@clinica.test'));

        $this->deleteJson("/api/v1/patients/{$patient->id}")->assertStatus(403);
    }

    public function test_overlapping_appointments_are_rejected(): void
    {
        $patient = $this->makePatient();
        $dentist = $this->makeDentist('den@clinica.test', 'MP-1');
        Sanctum::actingAs($this->makeUser('receptionist', 'rec@clinica.test'));

        $start = now()->addDay()->setTime(9, 0);
        $body  = [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'start_at'   => $start->toIso8601String(),
            'end_at'     => $start->copy()->addMinutes(45)->toIso8601String(),
        ];

        $this->postJson('/api/v1/appointments', $body)->assertCreated();

        $body['start_at'] = $start->copy()->addMinutes(30)->toIso8601String();
        $body['end_at']   = $start->copy()->addMinutes(75)->toIso8601String();
        $this->postJson('/api/v1/appointments', $body)->assertStatus(422)->assertJsonValidationErrors('start_at');
    }

    public function test_dentist_only_sees_own_appointments(): void
    {
        $patient = $this->makePatient();
        $d1 = $this->makeDentist('d1@clinica.test', 'MP-1');
        $d2 = $this->makeDentist('d2@clinica.test', 'MP-2');

        $admin = $this->makeUser('admin', 'adm@clinica.test');
        Sanctum::actingAs($admin);

        $start = now()->addDay()->setTime(9, 0);
        foreach ([$d1, $d2] as $i => $d) {
            $this->postJson('/api/v1/appointments', [
                'patient_id' => $patient->id,
                'dentist_id' => $d->id,
                'start_at'   => $start->toIso8601String(),
                'end_at'     => $start->copy()->addMinutes(30)->toIso8601String(),
            ])->assertCreated();
        }

        Sanctum::actingAs($d1->user);

        $this->getJson('/api/v1/appointments')->assertOk()->assertJsonCount(1, 'data');
    }
}
