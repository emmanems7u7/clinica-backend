<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DentistRequest;
use App\Http\Resources\DentistResource;
use App\Services\DentistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class DentistController extends Controller
{
    public function __construct(private DentistService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/dentists',
        summary: 'Listar odontólogos',
        security: [['sanctum' => []]],
        tags: ['Odontólogos'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', description: 'Texto a buscar', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'is_active', in: 'query', schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return DentistResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/dentists',
        summary: 'Crear odontólogo',
        security: [['sanctum' => []]],
        tags: ['Odontólogos'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Dra. Ana Rojas'),
            new OA\Property(property: 'email', type: 'string', example: 'ana.rojas@clinica.test'),
            new OA\Property(property: 'password', type: 'string', example: 'Cl1nica#Segura2026'),
            new OA\Property(property: 'phone', type: 'string', example: '70055555'),
            new OA\Property(property: 'license_number', type: 'string', example: 'MP-10234'),
            new OA\Property(property: 'specialty', type: 'string', example: 'Ortodoncia'),
            new OA\Property(property: 'is_active', type: 'boolean', example: true)
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(DentistRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new DentistResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/dentists/{id}',
        summary: 'Ver odontólogo',
        security: [['sanctum' => []]],
        tags: ['Odontólogos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): DentistResource
    {
        return new DentistResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/dentists/{id}',
        summary: 'Actualizar odontólogo',
        security: [['sanctum' => []]],
        tags: ['Odontólogos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Dra. Ana Rojas'),
            new OA\Property(property: 'email', type: 'string', example: 'ana.rojas@clinica.test'),
            new OA\Property(property: 'password', type: 'string', example: 'Cl1nica#Segura2026'),
            new OA\Property(property: 'phone', type: 'string', example: '70055555'),
            new OA\Property(property: 'license_number', type: 'string', example: 'MP-10234'),
            new OA\Property(property: 'specialty', type: 'string', example: 'Ortodoncia'),
            new OA\Property(property: 'is_active', type: 'boolean', example: true)
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(DentistRequest $request, int $id): DentistResource
    {
        $model = $this->service->find($id);

        return new DentistResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/dentists/{id}',
        summary: 'Desactivar odontólogo',
        security: [['sanctum' => []]],
        tags: ['Odontólogos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }
}
