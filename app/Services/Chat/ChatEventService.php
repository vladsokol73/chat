<?php

namespace App\Services\Chat;

use App\DTO\Chat\ChatListDto;
use App\DTO\Chat\MessageDto;
use App\DTO\Chat\MessageMediaDto;
use App\DTO\Chat\MessageSummaryDto;
use App\DTO\Client\ClientShortDto;
use App\Enums\MessageStatus;
use App\Events\ChatCreated;
use App\Events\ChatMessageCreated;
use App\Events\ChatUpdated;
use App\Models\Chat;
use App\Models\Client;
use App\Models\Integration;
use App\Models\Message;

class ChatEventService
{
    public function dispatchMessageCreated(Message $message): void
    {
        event(new ChatMessageCreated(
            new MessageDto(
                id: (string) $message->id,
                chatId: (string) $message->chat_id,
                userId: $message->user_id,
                direction: $message->direction,
                type: $message->type,
                status: $message->status instanceof MessageStatus
                    ? $message->status
                    : MessageStatus::from($message->status->value),
                text: $message->text ?? '',
                mediaGroupId: null,
                media: (function () use ($message) {
                    $mediaModels = $message->relationLoaded('media') ? $message->media : $message->media()->get();

                    return $mediaModels->isNotEmpty()
                        ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                        : null;
                })(),
                createdAt: now()->toIso8601String(),
            )
        ));
    }

    public function dispatchChatUpdated(Chat $chat, Message $message): void
    {
        $client = Client::find($chat->client_id);
        $integration = Integration::find($chat->integration_id);

        if (! $client || ! $integration) {
            return;
        }

        $lastMessage = new MessageSummaryDto(
            id: (string) $message->id,
            type: $message->type,
            text: $message->text ?? '',
            mediaGroupId: null,
            media: (function () use ($message) {
                $mediaModels = $message->relationLoaded('media') ? $message->media : $message->media()->get();

                return $mediaModels->isNotEmpty()
                    ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                    : null;
            })(),
            createdAt: now()->toIso8601String(),
        );

        $clientShort = new ClientShortDto(
            id: (string) $client->id,
            name: $client->name ?? 'Unknown',
            avatar: $client->avatar,
            tags: null,
        );

        event(new ChatUpdated(
            new ChatListDto(
                id: (string) $chat->id,
                client: $clientShort,
                lastMessage: $lastMessage,
                integrationName: $integration->name,
                unreadCount: 0,
                lastMessageAt: now()->toIso8601String(),
                status: $chat->status
            )
        ));
    }

    public function dispatchChatCreated(Chat $chat, Message $message): void
    {
        $client = Client::find($chat->client_id);
        $integration = Integration::find($chat->integration_id);

        if (! $client || ! $integration) {
            return;
        }

        // Последнее сообщение (summary)
        $lastMessage = new MessageSummaryDto(
            id: (string) $message->id,
            type: $message->type,
            text: $message->text ?? '',
            mediaGroupId: null,
            media: (function () use ($message) {
                $mediaModels = $message->relationLoaded('media') ? $message->media : $message->media()->get();

                return $mediaModels->isNotEmpty()
                    ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                    : null;
            })(),
            createdAt: now()->toIso8601String(),
        );

        // Короткая информация о клиенте
        $clientShort = new ClientShortDto(
            id: (string) $client->id,
            name: $client->name ?? 'Unknown',
            avatar: $client->avatar,
            tags: null,
        );

        // DTO самого чата
        $chatDto = new ChatListDto(
            id: (string) $chat->id,
            client: $clientShort,
            lastMessage: $lastMessage,
            integrationName: $integration->name,
            unreadCount: 1, // новый чат — всегда 1 непрочитанное
            lastMessageAt: now()->toIso8601String(),
            status: $chat->status,
        );

        event(new ChatCreated($chatDto));
    }
}
