<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\AppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/appointments',
        summary: 'Listar citas',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'dentist_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'patient_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', description: 'scheduled | confirmed | in_progress | completed | cancelled | no_show', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'date_from', in: 'query', description: 'YYYY-MM-DD', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'date_to', in: 'query', description: 'YYYY-MM-DD', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return AppointmentResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/appointments',
        summary: 'Crear cita',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
            new OA\Property(property: 'start_at', type: 'string', format: 'date-time', example: '2026-11-20T09:00:00-04:00'),
            new OA\Property(property: 'end_at', type: 'string', format: 'date-time', example: '2026-11-20T09:45:00-04:00'),
            new OA\Property(property: 'reason', type: 'string', example: 'Control y limpieza'),
            new OA\Property(property: 'notes', type: 'string', example: ''),
            new OA\Property(property: 'treatments', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'treatment_id', type: 'integer', example: 1), new OA\Property(property: 'tooth_number', type: 'string', example: '36'), new OA\Property(property: 'price', type: 'number', example: 150)]))
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(AppointmentRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new AppointmentResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/appointments/{id}',
        summary: 'Ver cita',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): AppointmentResource
    {
        return new AppointmentResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/appointments/{id}',
        summary: 'Actualizar cita',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'dentist_id', type: 'integer', example: 1),
            new OA\Property(property: 'start_at', type: 'string', format: 'date-time', example: '2026-11-20T09:00:00-04:00'),
            new OA\Property(property: 'end_at', type: 'string', format: 'date-time', example: '2026-11-20T09:45:00-04:00'),
            new OA\Property(property: 'reason', type: 'string', example: 'Control y limpieza'),
            new OA\Property(property: 'notes', type: 'string', example: ''),
            new OA\Property(property: 'treatments', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'treatment_id', type: 'integer', example: 1), new OA\Property(property: 'tooth_number', type: 'string', example: '36'), new OA\Property(property: 'price', type: 'number', example: 150)]))
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(AppointmentRequest $request, int $id): AppointmentResource
    {
        $model = $this->service->find($id);

        return new AppointmentResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/appointments/{id}',
        summary: 'Eliminar cita',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }

    #[OA\Patch(
        path: '/api/v1/appointments/{id}/status',
        summary: 'Cambiar el estado de una cita',
        security: [['sanctum' => []]],
        tags: ['Citas'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'status', type: 'string', example: 'confirmed'),
            new OA\Property(property: 'cancelled_reason', type: 'string', example: ''),
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function status(AppointmentStatusRequest $request, int $id): AppointmentResource
    {
        $appointment = $this->service->find($id);

        return new AppointmentResource($this->service->changeStatus(
            $appointment,
            $request->validated('status'),
            $request->validated('cancelled_reason'),
        ));
    }
}
