<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $email, string $password, string $ip, ?string $device = null): array
    {
        $key = Str::lower($email).'|'.$ip;

        if (RateLimiter::tooManyAttempts($key, config('clinic.login_max_attempts'))) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Demasiados intentos. Intente nuevamente en {$seconds} segundos.");
        }

        $user = User::where('email', $email)->first();

        // Mismo mensaje para email inexistente o clave errónea (no revelar cuál falló)
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($key, config('clinic.login_decay_seconds'));
            throw ValidationException::withMessages(['email' => ['Credenciales incorrectas.']]);
        }

        if (! $user->is_active) {
            abort(403, 'Usuario desactivado. Contacte al administrador.');
        }

        RateLimiter::clear($key);
        $user->forceFill(['last_login_at' => now()])->save();

        $expiresAt = now()->addMinutes(config('clinic.token_ttl_minutes'));
        $token     = $user->createToken($device ?: 'api', ['*'], $expiresAt);

        return [
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user'       => $user->load('roles'),
        ];
    }

    public function changePassword(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['La contraseña actual no es correcta.']]);
        }

        $user->update(['password' => $new]);

        // Cierra todas las demás sesiones (tokens) salvo la actual
        $current = $user->currentAccessToken();
        $user->tokens()->when($current?->id, fn ($q, $id) => $q->where('id', '!=', $id))->delete();
    }
}
