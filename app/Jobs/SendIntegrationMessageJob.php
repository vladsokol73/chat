<?php

namespace App\Jobs;

use App\Contracts\Outgoing\OutgoingMessageServiceInterface;
use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Models\Integration;
use App\Repositories\ChatRepository;
use App\Services\Chat\ChatEventService;
use App\Services\Chat\ChatService;
use App\Services\Integration\IntegrationRouter;
use App\Services\Message\MessageErrorService;
use App\Services\Message\MessageService;
use App\Services\Outgoing\OutgoingMediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendIntegrationMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $integrationId,
        public readonly SendIntegrationMessagePayloadDto $payload
    ) {
        $this->onQueue('integrations');
    }

    /**
     * @throws Throwable
     */
    public function handle(
        ChatService $chatService,
        MessageService $messageService,
        OutgoingMessageServiceInterface $outgoingService,
        ChatEventService $eventService,
        MessageErrorService $errorService,
        IntegrationRouter $router,
        ChatRepository $chatRepository,
        OutgoingMediaService $mediaService
    ): void {
        $integration = Integration::findOrFail($this->integrationId);

        // Chat по integration + external_id
        $chat = $chatRepository->findByIntegrationAndExternal(
            $integration,
            $this->payload->chat_id
        );

        // Создаём сообщение из DTO
        $message = $messageService->buildOutgoingMessage(
            $chat,
            $this->payload
        );

        try {
            // Показываем typing
            if ($this->payload->show_typing) {
                $outgoingService->sendTyping($integration, $this->payload->chat_id);
                sleep(2);
            }

            // Отправляем сообщение
            $result = $outgoingService->send(
                $message,
                $integration,
                $this->payload->toArray()
            );

            // Отмечаем отправленным
            $messageService->markSent($message, $result['message_id'] ?? null);

            // Обновляем чат
            $chatService->updateLastMessage($chat, $message);
            // Если чат был заблокирован и отправка прошла успешно — разблокируем (AUTO)
            $chatService->unblockIfSent($chat);

            // Dispatch events
            $eventService->dispatchMessageCreated($message);
            $eventService->dispatchChatUpdated($chat, $message);

        } catch (Throwable $e) {
            $errorService->handle($chat, $message, $integration, $e, $messageService, $chatService, $eventService);
        }
    }
}
