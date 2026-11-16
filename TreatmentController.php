<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TreatmentRequest;
use App\Http\Resources\TreatmentResource;
use App\Services\TreatmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class TreatmentController extends Controller
{
    public function __construct(private TreatmentService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/treatments',
        summary: 'Listar tratamientos',
        security: [['sanctum' => []]],
        tags: ['Tratamientos'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', description: 'Texto a buscar', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'is_active', in: 'query', schema: new OA\Schema(type: 'boolean')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return TreatmentResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/treatments',
        summary: 'Crear tratamiento',
        security: [['sanctum' => []]],
        tags: ['Tratamientos'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'code', type: 'string', example: 'LIM-001'),
            new OA\Property(property: 'name', type: 'string', example: 'Limpieza dental'),
            new OA\Property(property: 'description', type: 'string', example: 'Profilaxis completa'),
            new OA\Property(property: 'price', type: 'number', example: 150),
            new OA\Property(property: 'duration_minutes', type: 'integer', example: 45),
            new OA\Property(property: 'is_active', type: 'boolean', example: true)
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(TreatmentRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new TreatmentResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/treatments/{id}',
        summary: 'Ver tratamiento',
        security: [['sanctum' => []]],
        tags: ['Tratamientos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): TreatmentResource
    {
        return new TreatmentResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/treatments/{id}',
        summary: 'Actualizar tratamiento',
        security: [['sanctum' => []]],
        tags: ['Tratamientos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'code', type: 'string', example: 'LIM-001'),
            new OA\Property(property: 'name', type: 'string', example: 'Limpieza dental'),
            new OA\Property(property: 'description', type: 'string', example: 'Profilaxis completa'),
            new OA\Property(property: 'price', type: 'number', example: 150),
            new OA\Property(property: 'duration_minutes', type: 'integer', example: 45),
            new OA\Property(property: 'is_active', type: 'boolean', example: true)
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(TreatmentRequest $request, int $id): TreatmentResource
    {
        $model = $this->service->find($id);

        return new TreatmentResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/treatments/{id}',
        summary: 'Eliminar tratamiento',
        security: [['sanctum' => []]],
        tags: ['Tratamientos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }
}
