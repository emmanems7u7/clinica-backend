<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class PatientController extends Controller
{
    public function __construct(private PatientService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/patients',
        summary: 'Listar pacientes',
        security: [['sanctum' => []]],
        tags: ['Pacientes'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', description: 'Texto a buscar', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'gender', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return PatientResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/patients',
        summary: 'Crear paciente',
        security: [['sanctum' => []]],
        tags: ['Pacientes'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'first_name', type: 'string', example: 'Juan'),
            new OA\Property(property: 'last_name', type: 'string', example: 'Pérez'),
            new OA\Property(property: 'document_number', type: 'string', example: '1234567'),
            new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '1990-05-20'),
            new OA\Property(property: 'gender', type: 'string', example: 'male'),
            new OA\Property(property: 'phone', type: 'string', example: '70012345'),
            new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
            new OA\Property(property: 'address', type: 'string', example: 'Calle Comercio 123'),
            new OA\Property(property: 'emergency_contact_name', type: 'string', example: 'María Pérez'),
            new OA\Property(property: 'emergency_contact_phone', type: 'string', example: '70098765'),
            new OA\Property(property: 'allergies', type: 'string', example: 'Penicilina'),
            new OA\Property(property: 'medical_history', type: 'string', example: 'Hipertensión controlada'),
            new OA\Property(property: 'notes', type: 'string', example: '')
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(PatientRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new PatientResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/patients/{id}',
        summary: 'Ver paciente',
        security: [['sanctum' => []]],
        tags: ['Pacientes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): PatientResource
    {
        return new PatientResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/patients/{id}',
        summary: 'Actualizar paciente',
        security: [['sanctum' => []]],
        tags: ['Pacientes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'first_name', type: 'string', example: 'Juan'),
            new OA\Property(property: 'last_name', type: 'string', example: 'Pérez'),
            new OA\Property(property: 'document_number', type: 'string', example: '1234567'),
            new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '1990-05-20'),
            new OA\Property(property: 'gender', type: 'string', example: 'male'),
            new OA\Property(property: 'phone', type: 'string', example: '70012345'),
            new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
            new OA\Property(property: 'address', type: 'string', example: 'Calle Comercio 123'),
            new OA\Property(property: 'emergency_contact_name', type: 'string', example: 'María Pérez'),
            new OA\Property(property: 'emergency_contact_phone', type: 'string', example: '70098765'),
            new OA\Property(property: 'allergies', type: 'string', example: 'Penicilina'),
            new OA\Property(property: 'medical_history', type: 'string', example: 'Hipertensión controlada'),
            new OA\Property(property: 'notes', type: 'string', example: '')
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(PatientRequest $request, int $id): PatientResource
    {
        $model = $this->service->find($id);

        return new PatientResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/patients/{id}',
        summary: 'Eliminar paciente',
        security: [['sanctum' => []]],
        tags: ['Pacientes'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }
}
