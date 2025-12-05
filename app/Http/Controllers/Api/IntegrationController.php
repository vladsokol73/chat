<?php

namespace App\Http\Controllers\Api;

use App\DTO\Integration\IntegrationDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Integration\IntegrationCreateRequest;
use App\Http\Requests\Api\Integration\IntegrationUpdateRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Integration\IntegrationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly IntegrationService $integrationService
    ) {}

    #[OA\Get(
        path: '/api/integrations',
        operationId: 'listIntegrations',
        summary: 'Список интеграций',
        tags: ['Integration'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Integrations fetched successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/IntegrationDto')
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function index(): JsonResponse
    {
        $items = $this->integrationService->list();

        return ApiResponse::success(
            data: $items,
            message: 'Integrations fetched successfully'
        );
    }

    #[OA\Post(
        path: '/api/integrations',
        operationId: 'createIntegration',
        summary: 'Создать интеграцию',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/IntegrationDto')
        ),
        tags: ['Integration'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Integration created successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/IntegrationDto'
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function store(IntegrationCreateRequest $request): JsonResponse
    {
        $dto = IntegrationDto::fromArray($request->validated());

        $integration = $this->integrationService->create($dto);

        return ApiResponse::created(
            data: $integration,
            message: 'Integration created successfully'
        );
    }

    #[OA\Get(
        path: '/api/integrations/{id}',
        operationId: 'getIntegration',
        summary: 'Получить интеграцию по ID',
        tags: ['Integration'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID интеграции (UUID)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Integration fetched successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/IntegrationDto'
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $integration = $this->integrationService->getById($id);

        return ApiResponse::success(
            data: $integration,
            message: 'Integration fetched successfully'
        );
    }

    #[OA\Put(
        path: '/api/integrations/{id}',
        operationId: 'updateIntegration',
        summary: 'Обновить интеграцию',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/IntegrationDto')
        ),
        tags: ['Integration'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID интеграции (UUID)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Integration updated successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/IntegrationDto'
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function update(IntegrationUpdateRequest $request, string $id): JsonResponse
    {
        $dto = IntegrationDto::fromArray($request->validated());

        $integration = $this->integrationService->update($id, $dto);

        return ApiResponse::success(
            data: $integration,
            message: 'Integration updated successfully'
        );
    }

    #[OA\Delete(
        path: '/api/integrations/{id}',
        operationId: 'deleteIntegration',
        summary: 'Удалить интеграцию',
        tags: ['Integration'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID интеграции (UUID)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Integration deleted successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            nullable: true
                        ),
                    ]),
                ])
            ),

            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $this->integrationService->delete($id);

        return ApiResponse::success(
            data: null,
            message: 'Integration deleted successfully'
        );
    }
}
