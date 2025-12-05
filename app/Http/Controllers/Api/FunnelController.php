<?php

namespace App\Http\Controllers\Api;

use App\DTO\Funnel\FunnelDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Funnel\FunnelCreateRequest;
use App\Http\Requests\Api\Funnel\FunnelSendMessageRequest;
use App\Http\Requests\Api\Funnel\FunnelUpdateRequest;
use App\Http\Responses\ApiResponse;
use App\Services\FunnelService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Throwable;

class FunnelController extends Controller
{
    public function __construct(
        private readonly FunnelService $funnelService
    ) {}

    #[OA\Get(
        path: '/api/funnels',
        operationId: 'listFunnels',
        summary: 'Список воронок',
        tags: ['Funnel'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Funnels fetched successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/FunnelDto')
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function index(): JsonResponse
    {
        try {
            $items = $this->funnelService->list();

            return ApiResponse::success(
                data: $items,
                message: 'Funnels fetched successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to fetch funnels: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/funnels',
        operationId: 'createFunnel',
        summary: 'Создать воронку',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FunnelDto')
        ),
        tags: ['Funnel'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Funnel created successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/FunnelDto'
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function store(FunnelCreateRequest $request): JsonResponse
    {
        try {
            $dto = FunnelDto::fromArray($request->validated());
            $funnel = $this->funnelService->create($dto);

            return ApiResponse::created(
                data: $funnel,
                message: 'Funnel created successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to create funnel: '.$e->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/funnels/{id}',
        operationId: 'updateFunnel',
        summary: 'Обновить воронку',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FunnelDto')
        ),
        tags: ['Funnel'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '7b6f1a2b-3c4d-5e6f-7a8b-9c0d1e2f3a4b'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Funnel updated successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/FunnelDto'
                        ),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function update(FunnelUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $dto = FunnelDto::fromArray($request->validated());
            $funnel = $this->funnelService->update($id, $dto);

            return ApiResponse::success(
                data: $funnel,
                message: 'Funnel updated successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to update funnel: '.$e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/funnels/{id}',
        operationId: 'deleteFunnel',
        summary: 'Удалить воронку',
        tags: ['Funnel'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '7b6f1a2b-3c4d-5e6f-7a8b-9c0d1e2f3a4b'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Funnel deleted successfully',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(property: 'data', nullable: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Funnel deleted successfully'),
                    ]),
                ])
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->funnelService->delete($id);

            return ApiResponse::success(
                data: null,
                message: 'Funnel deleted successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to delete funnel: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/funnels/send-message',
        operationId: 'sendFunnelMessage',
        summary: 'Отправка сообщения пользователю из туннеля',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user', 'message'],
                properties: [
                    new OA\Property(property: 'user', description: 'ID чата / пользовательский идентификатор', type: 'string', example: '7250929534'),
                    new OA\Property(property: 'message', description: 'Текст сообщения', type: 'string', example: 'Привет! Как дела?'),
                ]
            )
        ),
        tags: ['Funnel'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Сообщение поставлено в очередь',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/UnauthorizedResponse', response: 401),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function sendMessage(FunnelSendMessageRequest $request): JsonResponse
    {
        try {
            $this->funnelService->sendMessageFromTunnel(
                chatId: $request->validated('user'),
                text: $request->validated('message'),
            );

            return ApiResponse::success(message: 'Message queued for delivery');
        } catch (Throwable $e) {
            return ApiResponse::notFound('Chat not found: '.$e->getMessage());
        }
    }
}
