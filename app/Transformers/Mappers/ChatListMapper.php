<?php

namespace App\Transformers\Mappers;

use App\DTO\Chat\ChatListDto;
use App\DTO\Chat\MessageMediaDto;
use App\DTO\Chat\MessageSummaryDto;
use App\DTO\Client\ClientShortDto;
use App\Enums\MessageType;
use App\Models\Chat;
use App\Models\Message;

class ChatListMapper
{
    public function __construct() {}

    public function map(Chat $chat): ChatListDto
    {
        $client = $chat->client;
        $clientDto = new ClientShortDto(
            id: $client ? (string) $client->id : (string) $chat->id,
            name: $client ? (string) $client->name : 'Unknown',
            avatar: $client ? $client->avatar : null,
            tags: null,
        );

        $last = $chat->lastMessage;
        if (! $last) {
            $last = Message::query()->where('chat_id', $chat->id)->latest('created_at')->first();
        }

        $createdAt = $last && $last->created_at
            ? $last->created_at->toIso8601String()
            : ($chat->last_message_at
                ? $chat->last_message_at->toIso8601String()
                : now()->toIso8601String());

        $media = null;
        $mediaGroupId = null;
        if ($last && is_array($last->payload)) {
            $mediaGroupId = $last->payload['normalized']['media_group_id'] ?? null;
        }
        if ($last) {
            $mediaModels = $last->relationLoaded('media') ? $last->media : $last->media()->get();
            if ($mediaModels->isNotEmpty()) {
                $media = array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all());
            }
        }

        $lastMessage = new MessageSummaryDto(
            id: $last ? (string) $last->id : (string) $chat->id,
            type: $last ? $last->type : MessageType::TEXT,
            text: $last ? (string) $last->text : '',
            mediaGroupId: $mediaGroupId,
            media: $media,
            createdAt: $createdAt,
        );

        return new ChatListDto(
            id: (string) $chat->id,
            client: $clientDto,
            lastMessage: $lastMessage,
            integrationName: $chat->integration->name,
            unreadCount: (int) ($chat->unread_count ?? 0),
            lastMessageAt: $chat->last_message_at
                ? $chat->last_message_at->toIso8601String()
                : $createdAt,
            status: (string) ($chat->status ?? 'open'),
        );
    }
}
