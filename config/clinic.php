<?php

return [
    // Vigencia del token de acceso (minutos). 480 = 8 horas (un turno de trabajo)
    'token_ttl_minutes' => (int) env('API_TOKEN_TTL_MINUTES', 480),

    // Intentos de login fallidos antes de bloquear (por email + IP) y tiempo de bloqueo (segundos)
    'login_max_attempts'  => (int) env('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 900),

    'currency' => env('CLINIC_CURRENCY', 'BOB'),
];
