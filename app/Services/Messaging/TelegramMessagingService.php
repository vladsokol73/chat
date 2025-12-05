<?php

namespace App\Services\Messaging;

use App\Contracts\Messaging\MediaExtractorInterface;
use App\Contracts\Messaging\MessagingServiceInterface;
use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\DTO\Messaging\Outgoing\ChatActionDto;
use App\DTO\Messaging\Outgoing\MediaMessageDto;
use App\DTO\Messaging\Outgoing\TextMessageDto;
use App\Enums\MediaType;
use App\Enums\Messaging\Incoming\IncomingMessageSource;
use App\Enums\Messaging\Incoming\IncomingMessageType;
use App\Enums\ServiceType;
use App\Models\Integration;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Types\Common\Update;
use Throwable;

readonly class TelegramMessagingService implements MessagingServiceInterface
{
    public function __construct(
        private MediaExtractorInterface $mediaExtractor,
    ) {}

    private function bot(string $token): Nutgram
    {
        try {
            return new Nutgram($token);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new RuntimeException("Failed to create Nutgram bot: {$e->getMessage()}");
        }
    }

    public function sendTextMessage(Integration $integration, TextMessageDto $dto): array
    {
        $bot = $this->bot($integration->token);

        $response = $bot->sendMessage(
            text: $dto->text,
            chat_id: $dto->chatId,
            parse_mode: $dto->parseMode?->value,
            disable_web_page_preview: $dto->disableWebPagePreview,
            disable_notification: $dto->disableNotification,
            reply_to_message_id: $dto->replyToMessageId,
        );

        return $response?->toArray() ?? [];
    }

    public function sendMediaMessage(Integration $integration, MediaMessageDto $dto): array
    {
        $bot = $this->bot($integration->token);

        return match ($dto->type) {
            MediaType::PHOTO => $bot->sendPhoto(
                photo: $dto->file,
                chat_id: $dto->chatId,
                caption: $dto->caption,
                parse_mode: $dto->parseMode?->value,
                disable_notification: $dto->disableNotification,
                reply_to_message_id: $dto->replyToMessageId,
            )?->toArray() ?? [],

            MediaType::VIDEO => $bot->sendVideo(
                video: $dto->file,
                chat_id: $dto->chatId,
                caption: $dto->caption,
                parse_mode: $dto->parseMode?->value,
                disable_notification: $dto->disableNotification,
            )?->toArray() ?? [],

            default => throw new RuntimeException("Unsupported media type: {$dto->type->value}")
        };
    }

    public function sendChatAction(Integration $integration, ChatActionDto $dto): bool
    {
        $bot = $this->bot($integration->token);

        return $bot->sendChatAction(
            action: $dto->action->value,
            chat_id: $dto->chatId,
        );
    }

    public function handleWebhook(Integration $integration, Request $request): ?IncomingMessageDto
    {
        $raw = json_decode($request->getContent(), true);

        if (! is_array($raw)) {
            return null;
        }

        $update = Update::fromArray($raw);

        $msg = $update->message ?? $update->edited_message ?? null;

        if (! $msg) {
            return null;
        }

        $media = $this->mediaExtractor->extract($msg);

        return new IncomingMessageDto(
            service: ServiceType::TELEGRAM,
            externalChatId: (string) $msg->chat->id,
            externalMessageId: (string) $msg->message_id,
            externalUserId: $msg->from?->id ? (string) $msg->from->id : null,

            source: $msg->from?->is_bot
                ? IncomingMessageSource::BOT
                : IncomingMessageSource::USER,

            type: $msg->text
                ? IncomingMessageType::TEXT
                : IncomingMessageType::MEDIA,

            text: $msg->text ?? $msg->caption,

            media: $media,

            sentAt: now()->toImmutable(),
            raw: $raw,
        );
    }

    public function setupWebhook(Integration $integration): void
    {
        $bot = $this->bot($integration->token);

        $webhookUrl = route('integration.webhook', [
            'integrationId' => $integration->id,
        ]);

        try {
            $result = $bot->setWebhook($webhookUrl);
            if ($result !== true) {
                throw new RuntimeException('Failed to set webhook: Telegram API returned false');
            }
        } catch (GuzzleException|JsonException|TelegramException $e) {
            throw new RuntimeException("Failed to set webhook: {$e->getMessage()}", previous: $e);
        }
    }

    public function validateIntegration(Integration $integration): bool
    {
        $bot = $this->bot($integration->token);

        try {
            $me = $bot->getMe();

            return $me !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
