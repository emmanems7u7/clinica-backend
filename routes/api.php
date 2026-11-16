<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicalRecordController;
use App\Http\Controllers\Api\DentistController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1  →  /api/v1/...
|--------------------------------------------------------------------------
| Seguridad: Sanctum (Bearer token) + usuario activo + permisos Spatie por ruta.
*/

// CRUD con un permiso por acción: {recurso}.view|create|update|delete
$crud = function (string $uri, string $controller, string $perm): void {
    Route::get($uri, [$controller, 'index'])->middleware("permission:$perm.view");
    Route::post($uri, [$controller, 'store'])->middleware("permission:$perm.create");
    Route::get("$uri/{id}", [$controller, 'show'])->whereNumber('id')->middleware("permission:$perm.view");
    Route::put("$uri/{id}", [$controller, 'update'])->whereNumber('id')->middleware("permission:$perm.update");
    Route::delete("$uri/{id}", [$controller, 'destroy'])->whereNumber('id')->middleware("permission:$perm.delete");
};

Route::prefix('v1')->group(function () use ($crud) {

    // Pública (con límite de intentos)
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Protegida
    Route::middleware(['auth:sanctum', 'active', 'throttle:api'])->group(function () use ($crud) {

        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::put('password', [AuthController::class, 'changePassword']);
        });

        Route::get('roles', [UserController::class, 'roles'])->middleware('permission:users.view');

        $crud('users', UserController::class, 'users');
        $crud('patients', PatientController::class, 'patients');
        $crud('dentists', DentistController::class, 'dentists');
        $crud('treatments', TreatmentController::class, 'treatments');
        $crud('appointments', AppointmentController::class, 'appointments');
        $crud('clinical-records', ClinicalRecordController::class, 'clinical-records');
        $crud('payments', PaymentController::class, 'payments');

        Route::patch('appointments/{id}/status', [AppointmentController::class, 'status'])
            ->whereNumber('id')
            ->middleware('permission:appointments.update');
    });
});
