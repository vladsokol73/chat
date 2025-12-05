<?php

namespace App\Services\Outgoing;

use App\Contracts\Outgoing\OutgoingMessageServiceInterface;
use App\DTO\Messaging\Outgoing\ChatActionDto;
use App\DTO\Messaging\Outgoing\MediaMessageDto;
use App\DTO\Messaging\Outgoing\TextMessageDto;
use App\Enums\MediaType;
use App\Models\Integration;
use App\Models\Media;
use App\Models\Message;
use App\Services\Integration\IntegrationRouter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

readonly class RealOutgoingMessageService implements OutgoingMessageServiceInterface
{
    public function __construct(
        private IntegrationRouter $router
    ) {}

    public function send(Message $message, Integration $integration, array $payload): array
    {
        $service = $this->router->resolve($integration);

        $mediaData = $payload['media'] ?? null;

        if (is_array($mediaData) && ! empty($mediaData)) {
            $mediaType = MediaType::from($mediaData['kind'] ?? 'photo');

            // Берём первое медиа из relation
            $mediaModel = $message->media()->first();

            // Получаем URL файла из S3 или используем public_url
            $fileUrl = null;

            if ($mediaModel instanceof Media && $mediaModel->path) {
                $fileUrl = Storage::disk('s3')->url($mediaModel->path);
            } elseif (isset($mediaData['public_url']) && is_string($mediaData['public_url'])) {
                $fileUrl = $mediaData['public_url'];
            }

            if (! $fileUrl) {
                throw new RuntimeException('Media file URL is required');
            }

            return $service->sendMediaMessage(
                $integration,
                new MediaMessageDto(
                    service: $integration->service,
                    chatId: (string) $payload['chat_id'],
                    type: $mediaType,
                    file: $fileUrl,
                    caption: ! empty($payload['text']) ? $payload['text'] : null,
                ),
            );
        }

        // Иначе отправляем текстовое сообщение
        return $service->sendTextMessage(
            $integration,
            new TextMessageDto(
                service: $integration->service,
                chatId: (string) $payload['chat_id'],
                text: $payload['text'],
            ),
        );
    }

    public function sendTyping(Integration $integration, string $chatId): void
    {
        $service = $this->router->resolve($integration);

        $service->sendChatAction(
            $integration,
            new ChatActionDto(
                service: $integration->service,
                chatId: $chatId
            )
        );
    }
}
