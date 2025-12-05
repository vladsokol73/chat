<?php

namespace App\Services\Inbound;

use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\Models\Chat;
use App\Models\Client;
use App\Models\Integration;
use App\Models\Message;
use App\Services\Chat\ChatEventService;
use App\Services\Chat\ChatService;
use App\Services\ClientService;
use App\Services\FunnelService;
use App\Services\Message\MessageService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class IncomingMessageService
{
    public function __construct(
        private ClientService $clientService,
        private ChatService $chatService,
        private MessageService $messageService,
        private ChatEventService $eventService,
        private FunnelService $funnelService,
        private MediaInboundService $mediaInboundService,
    ) {}

    /**
     * Главный метод — создание Client, Chat, Message + события + воронка.
     *
     * @throws Throwable
     */
    public function handle(Integration $integration, IncomingMessageDto $dto): void
    {
        DB::transaction(function () use ($integration, $dto) {

            // 1) Клиент
            $client = $this->clientService->findOrCreateFromIncoming($integration, $dto);

            // 2) Чат
            [$chat, $isNewChat, $hadMessagesBefore] = $this->chatService->findOrCreateChat($integration, $client, $dto);

            // 3) Идемпотентность
            $existing = $this->messageService->findExistingMessage($chat, $dto);
            if ($existing) {
                // просто обновляем last_message и шлём фронту
                $this->chatService->updateLastMessage($chat, $existing);
                $this->eventService->dispatchMessageCreated($existing);

                if ($isNewChat || ! $hadMessagesBefore) {
                    $this->eventService->dispatchChatCreated($chat, $existing);
                } else {
                    $this->eventService->dispatchChatUpdated($chat, $existing);
                }

                return;
            }

            // 4) Новое входящее сообщение
            $message = $this->messageService->createIncomingMessage($chat, $dto);

            // 4.1) Обработка медиа, если оно есть
            if ($dto->media !== null) {
                $this->mediaInboundService->handle($integration, $message, $dto->media);
            }

            // 5) Обновления чата
            $this->chatService->updateLastMessage($chat, $message);
            $chat->increment('unread_count');

            // 6) События
            if ($isNewChat || ! $hadMessagesBefore) {
                $this->eventService->dispatchChatCreated($chat, $message);
            } else {
                $this->eventService->dispatchChatUpdated($chat, $message);
            }

            $this->eventService->dispatchMessageCreated($message);

            // 7) Воронка (только для чатов со статусом auto)
            if ($this->chatService->shouldProcessWithFunnel($chat)) {
                $this->funnelService->bufferAndDispatch($integration, $chat, $message);
            }
        });
    }
}
