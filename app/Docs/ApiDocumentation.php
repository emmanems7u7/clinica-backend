<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Clínica API',
    description: 'Documentación de la API REST del sistema de clínica dental'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Servidor local'
)]
class ApiDocumentation
{
}