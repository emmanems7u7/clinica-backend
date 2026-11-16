<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Límite general de la API: 120 peticiones/min por usuario (o por IP si no está autenticado)
        RateLimiter::for('api', fn(Request $r) => Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));

        // Límite del login: 10 peticiones/min por IP (además del bloqueo por email+IP en AuthService)
        RateLimiter::for('login', fn(Request $r) => Limit::perMinute(10)->by($r->ip()));
    }
}
