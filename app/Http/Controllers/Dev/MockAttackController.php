<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dev\MockStartChatMessagesRequest;
use App\Http\Responses\ApiResponse;
use App\Jobs\GenerateChatsJob;
use App\Jobs\GenerateMessagesJob;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Throwable;

class MockAttackController extends Controller
{
    #[OA\Post(
        path: '/api/mock/attack/chats',
        operationId: 'generateChats',
        description: 'Запускает задачу, которая создаёт тестовые чаты и рассылает их по WebSocket (Reverb).',
        summary: 'Сгенерировать моковые чаты',
        tags: ['Mock'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mock chats generation started',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Mock chats generation started'
                                ),
                                new OA\Property(property: 'data', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function generateChats(): JsonResponse
    {
        try {
            GenerateChatsJob::dispatch(100, 20_00_000);

            return ApiResponse::success(message: 'Mock chats generation started');
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to start mock chat generation: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/mock/attack/chat',
        operationId: 'generateMessages',
        description: 'Создаёт набор тестовых сообщений. Если chatId не указан — сообщения генерируются для случайных чатов.',
        summary: 'Сгенерировать моковые сообщения (опционально в конкретном чате)',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/MockStartChatMessagesRequest')
        ),
        tags: ['Mock'],
        parameters: [
            new OA\Parameter(
                name: 'chatId',
                description: 'Опциональный ID чата (UUID). Если указан — сообщения генерируются в этот чат',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mock messages generation started',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Mock messages generation started'),
                                new OA\Property(property: 'data', nullable: true),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ServerErrorResponse', response: 500),
        ]
    )]
    public function generateMessages(MockStartChatMessagesRequest $request): JsonResponse
    {
        try {
            $options = $request->validated();

            GenerateMessagesJob::dispatch($options);

            return ApiResponse::success(message: 'Mock messages generation started');
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to start mock message generation: '.$e->getMessage());
        }
    }
}
