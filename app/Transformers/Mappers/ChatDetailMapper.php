<?php

namespace App\Transformers\Mappers;

use App\DTO\Chat\ChatDto;
use App\DTO\Chat\MessageDto;
use App\DTO\Chat\MessageMediaDto;
use App\DTO\Client\ClientDto;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Chat;
use App\Models\Message;
use Carbon\Carbon;

class ChatDetailMapper
{
    public function __construct() {}

    /** @param array<int, Message> $messages */
    public function map(Chat $chat, array $messages): ChatDto
    {
        $client = $chat->client;
        $clientDto = new ClientDto(
            id: $client ? (string) $client->id : (string) $chat->id,
            name: $client ? (string) $client->name : 'Unknown',
            avatar: $client ? $client->avatar : null,
            phone: $client ? (string) $client->phone : '',
            tags: null,
            comment: $client ? $client->comment : null,
        );

        $list = [];
        foreach ($messages as $m) {
            $media = null;
            $mediaGroupId = null;
            if (is_array($m->payload)) {
                $mediaGroupId = $m->payload['normalized']['media_group_id'] ?? null;
            }

            $mediaModels = $m->relationLoaded('media') ? $m->media : $m->media()->get();
            if ($mediaModels->isNotEmpty()) {
                $media = array_values($mediaModels->map(static fn ($mm) => MessageMediaDto::fromModel($mm))->all());
            }

            $list[] = new MessageDto(
                id: (string) $m->id,
                chatId: (string) $chat->id,
                userId: $m->user_id ? (string) $m->user_id : null,
                direction: $m->direction ?? MessageDirection::IN,
                type: $m->type ?? MessageType::TEXT,
                status: is_string($m->status)
                    ? MessageStatus::from($m->status)
                    : ($m->status ?? MessageStatus::QUEUED),
                text: (string) ($m->text ?? ''),
                mediaGroupId: $mediaGroupId,
                media: $media,
                createdAt: Carbon::make($m->sent_at)?->toIso8601String()
                    ?? Carbon::make($m->created_at)?->toIso8601String()
                    ?? now()->toIso8601String(),
            );
        }

        return new ChatDto(
            id: (string) $chat->id,
            client: $clientDto,
            messages: $list,
        );
    }
}
