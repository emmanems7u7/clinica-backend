<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service)
    {
    }

    #[OA\Get(
        path: '/api/v1/payments',
        summary: 'Listar pagos',
        security: [['sanctum' => []]],
        tags: ['Pagos'],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', description: 'Máx. 100', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'search', in: 'query', description: 'Texto a buscar', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'patient_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'appointment_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'method', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'order', in: 'query', description: 'asc | desc', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso')],
    )]
    public function index(Request $request)
    {
        return PaymentResource::collection($this->service->paginate($request->query()));
    }

    #[OA\Post(
        path: '/api/v1/payments',
        summary: 'Crear pago',
        security: [['sanctum' => []]],
        tags: ['Pagos'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'appointment_id', type: 'integer', example: 1),
            new OA\Property(property: 'amount', type: 'number', example: 150),
            new OA\Property(property: 'currency', type: 'string', example: 'BOB'),
            new OA\Property(property: 'method', type: 'string', example: 'cash'),
            new OA\Property(property: 'status', type: 'string', example: 'paid'),
            new OA\Property(property: 'reference', type: 'string', example: 'REC-0001'),
            new OA\Property(property: 'notes', type: 'string', example: '')
        ])),
        responses: [new OA\Response(response: 201, description: 'Creado'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function store(PaymentRequest $request): JsonResponse
    {
        $model = $this->service->create($request->validated());

        return (new PaymentResource($model))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/payments/{id}',
        summary: 'Ver pago',
        security: [['sanctum' => []]],
        tags: ['Pagos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function show(int $id): PaymentResource
    {
        return new PaymentResource($this->service->find($id));
    }

    #[OA\Put(
        path: '/api/v1/payments/{id}',
        summary: 'Actualizar pago',
        security: [['sanctum' => []]],
        tags: ['Pagos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'patient_id', type: 'integer', example: 1),
            new OA\Property(property: 'appointment_id', type: 'integer', example: 1),
            new OA\Property(property: 'amount', type: 'number', example: 150),
            new OA\Property(property: 'currency', type: 'string', example: 'BOB'),
            new OA\Property(property: 'method', type: 'string', example: 'cash'),
            new OA\Property(property: 'status', type: 'string', example: 'paid'),
            new OA\Property(property: 'reference', type: 'string', example: 'REC-0001'),
            new OA\Property(property: 'notes', type: 'string', example: '')
        ])),
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado'), new OA\Response(response: 422, description: 'Error de validación')],
    )]
    public function update(PaymentRequest $request, int $id): PaymentResource
    {
        $model = $this->service->find($id);

        return new PaymentResource($this->service->update($model, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/v1/payments/{id}',
        summary: 'Eliminar pago',
        security: [['sanctum' => []]],
        tags: ['Pagos'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Sin contenido'), new OA\Response(response: 401, description: 'No autenticado'), new OA\Response(response: 403, description: 'Sin permiso'), new OA\Response(response: 404, description: 'No encontrado')],
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($this->service->find($id));

        return response()->noContent();
    }
}
