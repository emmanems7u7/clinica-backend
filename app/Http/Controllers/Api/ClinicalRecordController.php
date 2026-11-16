<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClinicalRecordRequest;
use App\Http\Resources\ClinicalRecordResource;
use App\Services\ClinicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ClinicalRecordController extends Controller
{
    public function __construct(private ClinicalRecordService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/clinical-records',
        summary: 'Listar registros clínicos',
        security: [['sanctum' => []]],
        tags: ['Historias clínicas'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'patient_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'dentist_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'appointment_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return ClinicalRecordResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/clinical-records',
        summary: 'Crear registro clínico',
        security: [['sanctum' => []]],
        tags: ['Historias clínicas'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
            new OA\Property(property: 'appointment_id', type: 'integer', example: 1),
            new OA\Property(property: 'diagnosis', type: 'string', example: 'Caries profunda en pieza 36'),
            new OA\Property(property: 'procedure_notes', type: 'string', example: 'Se realizó endodoncia, conducto único'),
            new OA\Property(property: 'prescription', type: 'string', example: 'Ibuprofeno 400 mg cada 8 h por 3 días')
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(ClinicalRecordRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new ClinicalRecordResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/clinical-records/{id}',
        summary: 'Ver registro clínico',
        security: [['sanctum' => []]],
        tags: ['Historias clínicas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): ClinicalRecordResource
    {
        return new ClinicalRecordResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/clinical-records/{id}',
        summary: 'Actualizar registro clínico',
        security: [['sanctum' => []]],
        tags: ['Historias clínicas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
            new OA\Property(property: 'appointment_id', type: 'integer', example: 1),
            new OA\Property(property: 'diagnosis', type: 'string', example: 'Caries profunda en pieza 36'),
            new OA\Property(property: 'procedure_notes', type: 'string', example: 'Se realizó endodoncia, conducto único'),
            new OA\Property(property: 'prescription', type: 'string', example: 'Ibuprofeno 400 mg cada 8 h por 3 días')
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(ClinicalRecordRequest $request, int $id): ClinicalRecordResource
    {
        $model = $this->service->find($id);

        return new ClinicalRecordResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/clinical-records/{id}',
        summary: 'Eliminar registro clínico',
        security: [['sanctum' => []]],
        tags: ['Historias clínicas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }
}
