<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Iniciar sesión y obtener token',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'admin@clinica.test'),
                new OA\Property(property: 'password', type: 'string', example: 'ChangeMe!2026'),
                new OA\Property(property: 'device_name', type: 'string', example: 'swagger'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Token emitido'),
            new OA\Response(response: 403, description: 'Usuario desactivado'),
            new OA\Response(response: 422, description: 'Credenciales incorrectas'),
            new OA\Response(response: 429, description: 'Demasiados intentos'),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->service->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->ip(),
            $request->validated('device_name'),
        );

        return response()->json([
            'token'      => $data['token'],
            'token_type' => $data['token_type'],
            'expires_at' => $data['expires_at'],
            'user'       => new UserResource($data['user']),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: 'Usuario autenticado, roles y permisos',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado')],
    )]
    public function me(Request $request): UserResource
    {
        $user = $request->user()->load('roles');

        return (new UserResource($user))->additional([
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/auth/logout',
        summary: 'Cerrar sesión (revoca el token actual)',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        responses: [new OA\Response(response: 204, description: 'Sesión cerrada'), new OA\Response(response: 401, description: 'No autenticado')],
    )]
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    #[OA\Post(
        path: '/api/v1/auth/logout-all',
        summary: 'Cerrar todas las sesiones del usuario',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        responses: [new OA\Response(response: 204, description: 'Sesiones cerradas'), new OA\Response(response: 401, description: 'No autenticado')],
    )]
    public function logoutAll(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    #[OA\Put(
        path: '/api/v1/auth/password',
        summary: 'Cambiar mi contraseña (cierra las demás sesiones)',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'current_password', type: 'string'),
            new OA\Property(property: 'password', type: 'string'),
            new OA\Property(property: 'password_confirmation', type: 'string'),
        ])),
        responses: [new OA\Response(response: 204, description: 'Contraseña actualizada'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function changePassword(ChangePasswordRequest $request): Response
    {
        $this->service->changePassword($request->user(), $request->validated('current_password'), $request->validated('password'));

        return response()->noContent();
    }
}
