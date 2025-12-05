<?php

namespace App\Http\Controllers\Api;

use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Enums\ChatStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chat\ChatDetailRequest;
use App\Http\Requests\Api\Chat\GetChatsRequest;
use App\Http\Requests\Api\Chat\MarkChatMessagesReadRequest;
use App\Http\Requests\Api\Chat\SendChatMessageRequest;
use App\Http\Requests\Api\Chat\UpdateChatStatusRequest;
use App\Http\Requests\Api\Chat\UploadChatMediaRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Chat;
use App\Services\Chat\ChatService;
use App\Services\Message\MessageQueueService;
use App\Services\Outgoing\MediaOutboundService;
use Illuminate\Http\UploadedFile;
use OpenApi\Attributes as OA;
use Throwable;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
        private readonly MessageQueueService $messageQueueService,
        private readonly MediaOutboundService $mediaOutboundService,
    ) {}

    #[OA\Get(
        path: '/api/chats',
        operationId: 'getChats',
        summary: 'Список чатов (cursor pagination)',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_CursorParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_LimitParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_DirectionParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_SortParam'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chats fetched successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/ChatListCursorPaginatedDto'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function list(GetChatsRequest $request)
    {
        try {
            $cursor = $request->query('cursor');
            $limit = (int) $request->query('limit', 25);
            $direction = $request->query('direction', 'down');
            $sort = (string) $request->query('sort', 'all');
            $userId = auth()->id() ? (string) auth()->id() : null;

            // Сервис тут ДОЛЖЕН вернуть DTO (CursorPaginatedDto)
            $result = $this->chatService->getChatList(
                cursor: $cursor,
                limit: $limit,
                direction: $direction,
                sort: $sort,
                userId: $userId
            );

            return ApiResponse::success(
                data: $result,
                message: 'Chats fetched successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError(
                message: 'Failed to fetch chats: '.$e->getMessage()
            );
        }
    }

    #[OA\Get(
        path: '/api/chats/{chatId}',
        operationId: 'getChatById',
        summary: 'Детали чата (сообщения с cursor pagination)',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ChatDetailRequest_ChatIdParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_CursorParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_LimitParam'),
            new OA\Parameter(ref: '#/components/parameters/GetChatsRequest_DirectionParam'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat detail fetched',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/ChatDetailCursorPaginatedDto'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function detail(ChatDetailRequest $request)
    {
        try {
            $cursor = $request->query('cursor');
            $limit = (int) $request->query('limit', 20);
            $direction = $request->query('direction', 'down');

            $payload = $this->chatService->getChatDetailPaginated(
                chatId: $request->chatId,
                cursor: $cursor,
                limit: $limit,
                direction: $direction
            );

            return ApiResponse::success(data: $payload, message: 'Chat detail fetched');
        } catch (Throwable $e) {
            return ApiResponse::notFound('Chat not found: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/chats/{chatId}/messages',
        operationId: 'sendChatMessage',
        summary: 'Отправка текстового сообщения (с optional-медиа) в чат',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['text'],
                properties: [
                    new OA\Property(
                        property: 'text',
                        type: 'string',
                        maxLength: 4096,
                        example: 'Привет! Как дела?'
                    ),
                    new OA\Property(
                        property: 'messageId',
                        type: 'string',
                        format: 'uuid',
                        example: '123e4567-e89b-12d3-a456-426614174000'
                    ),
                    new OA\Property(
                        property: 'media_ids',
                        description: 'Массив ID временных медиа (media.id), загруженных через /chats/{chatId}/media',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'uuid'),
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'chatId',
                description: 'UUID чата',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Message enqueued successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function sendMessage(SendChatMessageRequest $request)
    {
        try {
            $chatId = $request->validated('chatId');
            $messageId = $request->validated('messageId');
            $text = $request->validated('text');
            /** @var array<int,string>|null $mediaIds */
            $mediaIds = $request->validated('media_ids') ?? null;

            // Находим чат
            $chat = Chat::query()->findOrFail($chatId);

            // Отправляем сообщение в очередь
            $this->messageQueueService->dispatchOutgoing(
                $chat->integration_id,
                new SendIntegrationMessagePayloadDto(
                    chat_id: $chat->external_id,
                    text: $text,
                    user_id: auth()->id(),
                    show_typing: false,
                    message_id: $messageId,
                    funnel_metadata: null,
                    media_ids: $mediaIds,
                )
            );

            return ApiResponse::success(message: 'Message enqueued');
        } catch (Throwable $e) {
            return ApiResponse::notFound('Chat not found: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/chats/{chatId}/read',
        operationId: 'markChatMessagesRead',
        summary: 'Пометить все непрочитанные сообщения в чате как прочитанные',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'chatId',
                description: 'UUID чата',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Messages marked as read successfully'
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function markMessagesAsRead(MarkChatMessagesReadRequest $request)
    {
        try {
            $chatId = $request->validated('chatId');

            // Помечаем все непрочитанные сообщения как прочитанные
            $this->chatService->markChatAsReadById($chatId);

            return ApiResponse::success(message: 'Messages have been read');
        } catch (Throwable $e) {
            return ApiResponse::notFound('Chat not found: '.$e->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/chats/{chatId}/status',
        operationId: 'updateChatStatus',
        summary: 'Обновить статус чата',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['auto', 'manual', 'blocked'],
                        example: 'manual'
                    ),
                ]
            )
        ),
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'chatId',
                description: 'UUID чата',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat status updated successfully'
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function updateStatus(UpdateChatStatusRequest $request)
    {
        try {
            $chatId = $request->validated('chatId');
            $status = ChatStatus::from($request->validated('status'));

            // Обновляем статус
            $this->chatService->updateStatus($chatId, $status);

            return ApiResponse::success(message: 'Chat status updated successfully');
        } catch (Throwable $e) {
            return ApiResponse::notFound('Chat not found: '.$e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/chats/media',
        operationId: 'uploadChatMedia',
        summary: 'Загрузка медиа-файла для последующей отправки сообщения',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            description: 'Файл медиа (изображение, видео, документ и т.д.)',
                            type: 'string',
                            format: 'binary'
                        ),
                    ]
                )
            )
        ),
        tags: ['Chat'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Media uploaded successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/MessageMediaDto',
                                    description: 'Информация о загруженном медиа'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function uploadMedia(UploadChatMediaRequest $request)
    {
        try {
            /** @var UploadedFile $file */
            $file = $request->file('file');

            $mediaDto = $this->mediaOutboundService->uploadFile($file);

            return ApiResponse::success(
                data: $mediaDto->toArray(),
                message: 'Media uploaded successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError('Failed to upload media: '.$e->getMessage());
        }
    }
}
