<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Spatie\Permission\Models\Role;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(private UserService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/users',
        summary: 'Listar usuarios',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', description: 'Texto a buscar', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'is_active', in: 'query', schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return UserResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/users',
        summary: 'Crear usuario',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Carla Mamani'),
            new OA\Property(property: 'email', type: 'string', example: 'carla@clinica.test'),
            new OA\Property(property: 'password', type: 'string', example: 'Cl1nica#Segura2026'),
            new OA\Property(property: 'phone', type: 'string', example: '70011122'),
            new OA\Property(property: 'is_active', type: 'boolean', example: true),
            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['receptionist'])
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(UserRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new UserResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/users/{id}',
        summary: 'Ver usuario',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): UserResource
    {
        return new UserResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/users/{id}',
        summary: 'Actualizar usuario',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Carla Mamani'),
            new OA\Property(property: 'email', type: 'string', example: 'carla@clinica.test'),
            new OA\Property(property: 'password', type: 'string', example: 'Cl1nica#Segura2026'),
            new OA\Property(property: 'phone', type: 'string', example: '70011122'),
            new OA\Property(property: 'is_active', type: 'boolean', example: true),
            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['receptionist'])
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(UserRequest $request, int $id): UserResource
    {
        $model = $this->service->find($id);

        return new UserResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/users/{id}',
        summary: 'Desactivar usuario',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }

    #[OA\Get(
        path: '/api/v1/roles',
        summary: 'Listar roles y sus permisos',
        security: [['sanctum' => []]],
        tags: ['Usuarios'],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function roles(): JsonResponse
    {
        $roles = Role::with('permissions:id,name')->orderBy('name')->get()->map(fn (Role $r) => [
            'name'        => $r->name,
            'permissions' => $r->permissions->pluck('name')->values(),
        ]);

        return response()->json(['data' => $roles]);
    }
}
