<?php

namespace App\Services\Message;

use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\Messaging\Incoming\IncomingMessageType;
use App\Models\Chat;
use App\Models\Message;
use App\Services\Outgoing\MediaOutboundService;
use Throwable;

class MessageService
{
    public function __construct(
        private readonly MediaOutboundService $mediaOutboundService,
    ) {}

    /**
     * @throws Throwable
     */
    public function buildOutgoingMessage(Chat $chat, SendIntegrationMessagePayloadDto $payload): Message
    {
        $funnel = $payload->funnel_metadata;

        // Собираем payload для сохранения в JSON
        $messagePayload = [
            'chat_id' => $payload->chat_id,
            'text' => $payload->text,
            'user_id' => $payload->user_id,
            'show_typing' => $payload->show_typing,
            'message_id' => $payload->message_id,
        ];

        // Если есть funnel metadata, добавляем поля в payload
        if ($funnel) {
            $messagePayload['dify_message_id'] = $funnel->dify_message_id;
            $messagePayload['dify_conversation_id'] = $funnel->dify_conversation_id;
            $messagePayload['price'] = $funnel->price;
            $messagePayload['sequence_index'] = $funnel->sequence_index;
            $messagePayload['sequence_total'] = $funnel->sequence_total;
        }

        // Определяем тип сообщения
        $messageType = $funnel !== null
            ? MessageType::FUNNEL->value
            : MessageType::TEXT->value;

        /** @var Message $message */
        $message = Message::create([
            'id' => $payload->message_id,
            'chat_id' => $chat->id,
            'user_id' => $payload->user_id,
            'direction' => MessageDirection::OUT->value,
            'type' => $messageType,
            'status' => MessageStatus::QUEUED->value,
            'text' => $payload->text,
            'payload' => $messagePayload,
            'price' => $funnel->price ?? 0,
            'reply_to_message_id' => null,
            'sent_at' => now(),
        ]);

        // Если есть медиа — привязываем их к сообщению
        if (! empty($payload->media_ids)) {
            $attachedMediaDtos = $this->mediaOutboundService->attachToMessage(
                $message,
                $payload->media_ids,
            );

            // Если прикрепили медиа — можем уточнить тип сообщения по первому медиа
            if ($attachedMediaDtos !== []) {
                $first = $attachedMediaDtos[0];
                $message->type = $this->mapMediaKindToMessageType($first->kind);
                $message->save();
            }
        }

        return $message;
    }

    public function createIncoming(Chat $chat, array $data): Message
    {
        return Message::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'direction' => MessageDirection::IN->value,
            'type' => $data['type'] ?? MessageType::TEXT->value,
            'status' => MessageStatus::DELIVERED->value,
            'text' => $data['text'] ?? '',
            'payload' => $data['payload'] ?? [],
            'external_message_id' => $data['external_message_id'] ?? null,
            'reply_to_message_id' => $data['reply_to_message_id'] ?? null,
            'sent_at' => $data['sent_at'] ?? now(),
            'delivered_at' => now(),
        ]);
    }

    public function markSent(Message $message, string $externalMessageId): void
    {
        $message->updateQuietly([
            'external_message_id' => $externalMessageId,
            'status' => MessageStatus::SENT->value,
            'delivered_at' => now(),
        ]);
    }

    public function fail(Message $message, string $error, string|int|null $code): void
    {
        $message->updateQuietly([
            'status' => MessageStatus::FAILED->value,
            'error_code' => (string) $code,
            'error_message' => substr($error, 0, 512),
        ]);
    }

    public function createSystem(Chat $chat, string $text, array $payload = []): Message
    {
        return Message::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'direction' => MessageDirection::OUT->value,
            'type' => MessageType::SYSTEM->value,
            'status' => MessageStatus::DELIVERED->value,
            'text' => $text,
            'payload' => $payload,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    public function findExistingMessage(Chat $chat, IncomingMessageDto $dto): ?Message
    {
        return Message::query()
            ->where('chat_id', $chat->id)
            ->where('external_message_id', $dto->externalMessageId)
            ->first();
    }

    public function createIncomingMessage(Chat $chat, IncomingMessageDto $dto): Message
    {
        $type = match ($dto->type) {
            IncomingMessageType::TEXT => MessageType::TEXT->value,
            // IncomingMessageType::MEDIA => MessageType::MEDIA->value,
            default => MessageType::TEXT->value,
        };

        return Message::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'direction' => MessageDirection::IN->value,
            'type' => $type,
            'status' => MessageStatus::DELIVERED->value,
            'text' => $dto->text ?? '',
            'payload' => [
                'raw' => $dto->raw,
                'media' => $dto->media,
            ],
            'external_message_id' => $dto->externalMessageId,
            'reply_to_message_id' => null,
            'sent_at' => $dto->sentAt ?? now(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * Map media kind to MessageType
     */
    private function mapMediaKindToMessageType(string $kind): MessageType
    {
        return match ($kind) {
            'photo' => MessageType::PHOTO,
            'video' => MessageType::VIDEO,
            'audio' => MessageType::AUDIO,
            'voice' => MessageType::VOICE,
            'document' => MessageType::DOCUMENT,
            'animation' => MessageType::ANIMATION,
            'sticker' => MessageType::STICKER,
            'video_note' => MessageType::VIDEO_NOTE,
            default => MessageType::TEXT,
        };
    }
}
