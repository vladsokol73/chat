<?php

namespace App\Services\Chat;

use App\DTO\Chat\ChatDto;
use App\DTO\Chat\MessageDto;
use App\DTO\Client\ClientDto;
use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\DTO\Pagination\CursorPaginatedDto;
use App\Enums\ChatStatus;
use App\Models\Chat;
use App\Models\Client;
use App\Models\Integration;
use App\Models\Message;
use App\Repositories\ChatRepository;
use App\Services\FunnelService;
use App\Transformers\Mappers\ChatDetailMapper;
use App\Transformers\Mappers\ChatListMapper;
use RuntimeException;

readonly class ChatService
{
    public function __construct(
        private ChatListMapper $chatListMapper,
        private ChatDetailMapper $chatDetailMapper,
        private ChatRepository $chatRepository,
        private FunnelService $funnelService,
    ) {}

    public function getChatList(
        ?string $cursor,
        int $limit,
        string $direction = 'down',
        string $sort = 'all',
        ?string $userId = null
    ): CursorPaginatedDto {
        $limit = max(1, min(100, $limit));

        $paginator = $this->chatRepository->paginateWithCursor(
            cursor: $cursor,
            limit: $limit,
            direction: $direction,
            sort: $sort,
            userId: $userId
        );

        $items = array_map(
            fn ($chat) => $this->chatListMapper->map($chat)->toArray(),
            $paginator->items()
        );

        return new CursorPaginatedDto(
            items: $items,
            nextCursor: $paginator->nextCursor()?->encode(),
            prevCursor: $paginator->previousCursor()?->encode(),
        );
    }

    public function getChatDetail(string $chatId): ChatDto
    {
        $dbChat = Chat::query()->with(['client'])->find($chatId);

        if (! $dbChat) {
            throw new RuntimeException('Chat not found');
        }

        $messagesModels = Message::query()
            ->where('chat_id', $dbChat->id)
            ->with('media')
            ->orderBy('created_at')
            ->orderBy('external_message_id')
            ->limit(200)
            ->get()
            ->all();

        return $this->chatDetailMapper->map($dbChat, $messagesModels);
    }

    public function getChatDetailPaginated(
        string $chatId,
        ?string $cursor,
        int $limit,
        string $direction = 'down',
    ): array {
        $limit = max(1, min(200, $limit));

        /** @var Chat|null $dbChat */
        $dbChat = Chat::query()
            ->with(['client'])
            ->find($chatId);

        if (! $dbChat) {
            throw new RuntimeException('Chat not found');
        }

        $query = Message::query()
            ->where('chat_id', $dbChat->id)
            ->with('media');

        if ($direction === 'up') {
            // Читаем "вверх" — от старых к новым.
            $query
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc');
        } else {
            // Читаем "вниз" — от новых к старым (по-умолчанию).
            $direction = 'down';

            $query
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
        }

        $paginator = $query->cursorPaginate(
            perPage: $limit,
            cursor: $cursor,
        );

        $items = array_map(
            static fn (Message $message) => MessageDto::fromModel($message)->toArray(),
            $paginator->items()
        );

        if ($direction === 'down') {
            $items = array_reverse($items);
        }

        $client = $dbChat->client;
        $clientDto = new ClientDto(
            id: $client ? (string) $client->id : (string) $dbChat->id,
            name: $client ? (string) $client->name : 'Unknown',
            avatar: $client ? $client->avatar : null,
            phone: $client ? (string) $client->phone : '',
            tags: null,
            comment: $client ? $client->comment : null,
        );

        return [
            'id' => (string) $dbChat->id,
            'client' => $clientDto->toArray(),
            'messages' => [
                'items' => $items,
                'nextCursor' => $paginator->nextCursor()?->encode(),
                'prevCursor' => $paginator->previousCursor()?->encode(),
            ],
        ];
    }

    public function updateLastMessage(Chat $chat, Message $message): void
    {
        $chat->updateQuietly([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function blockChat(Chat $chat): void
    {
        $chat->updateQuietly([
            'status' => ChatStatus::BLOCKED->value,
            'updated_at' => now(),
        ]);
    }

    /**
     * If chat was blocked and an outgoing send succeeds, switch it back to AUTO.
     */
    public function unblockIfSent(Chat $chat): void
    {
        if ($chat->status === ChatStatus::BLOCKED->value) {
            $chat->updateQuietly([
                'status' => ChatStatus::AUTO->value,
                'updated_at' => now(),
            ]);
        }
    }

    public function findOrCreateChat(Integration $integration, Client $client, IncomingMessageDto $dto): array
    {
        $hasFunnel = $this->funnelService->hasFunnel($integration);

        $chat = Chat::query()->firstOrCreate(
            [
                'integration_id' => $integration->id,
                'external_id' => (string) $dto->externalChatId,
            ],
            [
                'client_id' => $client->id,
                'channel' => $dto->service->value,
                'status' => $hasFunnel ? ChatStatus::AUTO->value : ChatStatus::MANUAL->value,
                'unread_count' => 0,
            ]
        );

        $isNewChat = $chat->wasRecentlyCreated;
        $hadMessagesBefore = Message::query()->where('chat_id', $chat->id)->exists();

        return [$chat, $isNewChat, $hadMessagesBefore];
    }

    /**
     * Mark all unread messages in chat as read
     */
    public function markMessagesAsRead(Chat $chat): int
    {
        return Message::query()
            ->where('chat_id', $chat->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Reset unread count for chat
     */
    public function resetUnreadCount(Chat $chat): void
    {
        $chat->update(['unread_count' => 0]);
    }

    public function markChatAsReadById(string $chatId): void
    {
        $chat = Chat::query()->findOrFail($chatId);

        $this->markMessagesAsRead($chat);

        $this->resetUnreadCount($chat);
    }

    /**
     * Update chat status
     */
    public function updateStatus(string $chatId, ChatStatus $status): void
    {
        $chat = Chat::query()->findOrFail($chatId);

        $chat->updateQuietly([
            'status' => $status->value,
            'updated_at' => now(),
        ]);
    }

    /**
     * Check if chat should be processed with funnel (automatic processing)
     */
    public function shouldProcessWithFunnel(Chat $chat): bool
    {
        return $chat->status === ChatStatus::AUTO->value;
    }
}
