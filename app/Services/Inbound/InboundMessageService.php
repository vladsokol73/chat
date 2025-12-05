<?php

namespace App\Services\Inbound;

use App\DTO\Chat\ChatListDto;
use App\DTO\Chat\MessageDto;
use App\DTO\Chat\MessageMediaDto;
use App\DTO\Chat\MessageSummaryDto;
use App\DTO\Client\ClientShortDto;
use App\Enums\ChatStatus;
use App\Enums\MessageStatus;
use App\Events\ChatCreated;
use App\Events\ChatMessageCreated;
use App\Events\ChatUpdated;
use App\Models\Chat;
use App\Models\Client;
use App\Models\Integration;
use App\Models\Message;
use App\Services\Channels\TelegramUpdateNormalizer;
use App\Services\FunnelService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

readonly class InboundMessageService
{
    public function __construct(
        private TelegramUpdateNormalizer $normalizer,
        private FunnelService $funnelService,
    ) {}

    public function handleTelegramUpdate(string $integrationId, array $update): void
    {
        $normalized = $this->normalizer->normalize($update);
        if ($normalized === null) {
            return;
        }

        $externalChatId = (int) abs((int) $normalized['chat_id']);

        /** @var Integration $integration */
        $integration = Integration::query()->findOrFail($integrationId);

        // Client
        $from = $update['message']['from'] ?? [];
        $chatObj = $update['message']['chat'] ?? [];

        $first = $from['first_name'] ?? $chatObj['first_name'] ?? null;
        $last = $from['last_name'] ?? $chatObj['last_name'] ?? null;
        $username = $from['username'] ?? $chatObj['username'] ?? null;

        $displayName = trim(implode(' ', array_filter([$first, $last])));
        if ($displayName === '') {
            $displayName = $username ? (string) $username : ('Telegram User '.substr((string) $externalChatId, -4));
        }
        $phone = $username ? ('@'.$username) : '';

        try {
            $client = Client::query()->firstOrCreate(
                [
                    'integration_id' => $integration->id,
                    'external_id' => $externalChatId,
                ],
                [
                    'name' => $displayName,
                    'phone' => $phone,
                    'avatar' => '',
                ]
            );
            if (empty($client->id)) {
                $client->refresh();
            }
        } catch (QueryException $e) {
            Log::warning('inbound.client.create_failed', ['error' => $e->getMessage()]);
            $client = Client::query()->where([
                'integration_id' => $integration->id,
                'external_id' => $externalChatId,
            ])->firstOrFail();
        }

        // Подправим существующего клиента, если имя/phone были плейсхолдерами
        $updates = [];
        if (isset($client->name) && str_starts_with($client->name, 'Telegram User ') && $displayName !== $client->name) {
            $updates['name'] = $displayName;
        }
        if ((string) ($client->phone ?? '') === '' && $phone !== '') {
            $updates['phone'] = $phone;
        }
        if ($updates) {
            $client->update($updates);
        }

        // Chat
        $isNewChat = false;
        $hasFunnel = $this->funnelService->hasFunnel($integration);

        try {
            $chat = Chat::query()->firstOrCreate(
                [
                    'integration_id' => $integration->id,
                    'external_id' => (string) $normalized['chat_id'],
                ],
                [
                    'client_id' => $client->id,
                    'channel' => 'telegram',
                    'status' => $hasFunnel ? ChatStatus::AUTO->value : ChatStatus::MANUAL->value,
                    'unread_count' => 0,
                ]
            );
            $isNewChat = (bool) $chat->wasRecentlyCreated;
            if (empty($chat->id)) {
                $chat->refresh();
            }
        } catch (QueryException $e) {
            Log::warning('inbound.chat.create_failed', ['error' => $e->getMessage()]);
            $chat = Chat::query()->where([
                'integration_id' => $integration->id,
                'external_id' => (string) $normalized['chat_id'],
            ])->firstOrFail();
        }

        if (empty($chat->id)) {
            Log::error('inbound.chat.id_missing_after_create', [
                'integration_id' => $integration->id,
                'external_id' => (string) $normalized['chat_id'],
            ]);
            throw new RuntimeException('Chat id is missing after creation');
        }

        // Определим, была ли переписка до текущего апдейта (нужно для выбора chat.created vs chat.updated)
        $hadMessagesBefore = Message::query()->where('chat_id', $chat->id)->exists();

        // Messages (idempotent)
        $externalMessageId = (string) ($normalized['external_message_id'] ?? ($update['message']['message_id'] ?? ''));

        $replyToInternalId = null;
        $replyExternalId = $normalized['reply_to_external_message_id'] ?? ($update['message']['reply_to_message']['message_id'] ?? null);
        if ($replyExternalId !== null) {
            $reply = Message::query()
                ->where('chat_id', $chat->id)
                ->where('external_message_id', (string) $replyExternalId)
                ->first();
            if ($reply) {
                $replyToInternalId = $reply->id;
            }
        }

        $existing = Message::query()
            ->where('chat_id', $chat->id)
            ->where('external_message_id', $externalMessageId)
            ->first();

        if ($existing) {
            DB::table('chats')
                ->where('id', $chat->id)
                ->update([
                    'last_message_id' => $existing->id,
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);

            $existingMediaGroupId = null;
            if (is_array($existing->payload)) {
                $existingMediaGroupId = $existing->payload['normalized']['media_group_id'] ?? null;
            }
            $existingMedia = (function () use ($existing) {
                $mediaModels = $existing->relationLoaded('media') ? $existing->media : $existing->media()->get();

                return $mediaModels->isNotEmpty()
                    ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                    : null;
            })();
            $retryMessageDto = new MessageDto(
                id: (string) $existing->id,
                chatId: (string) $chat->id,
                userId: (string) $client->id,
                direction: (string) ($existing->direction ?? 'in'),
                type: (string) ($existing->type ?? 'text'),
                status: $existing->status instanceof MessageStatus
                    ? $existing->status
                    : MessageStatus::from($existing->status ?? MessageStatus::DELIVERED->value),
                text: (string) $existing->text,
                mediaGroupId: $existingMediaGroupId,
                media: $existingMedia,
                createdAt: now()->toIso8601String(),
            );
            event(new ChatMessageCreated($retryMessageDto));

            $this->broadcastChatListEvent($chat, $existing, $client, $externalChatId, $isNewChat || ! $hadMessagesBefore);

            return;
        }

        // Create new message
        $sentAt = null;
        $rawDate = $update['message']['date'] ?? null;
        if (is_int($rawDate) || (is_string($rawDate) && ctype_digit($rawDate))) {
            $sentAt = Carbon::createFromTimestamp((int) $rawDate);
        }

        try {
            $message = Message::query()->create([
                'chat_id' => $chat->id,
                'user_id' => null,
                'external_message_id' => $externalMessageId,
                'direction' => 'in',
                'type' => (string) ($normalized['type'] ?? 'text'),
                'status' => MessageStatus::DELIVERED->value,
                'text' => (string) ($normalized['text'] ?? ''),
                'payload' => array_merge($update, ['normalized' => $normalized]),
                'reply_to_message_id' => $replyToInternalId,
                'sent_at' => $sentAt,
                'delivered_at' => now(),
                'read_at' => null,
            ]);
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) !== '23505') {
                Log::error('inbound.message.create_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
            $message = Message::query()
                ->where('chat_id', $chat->id)
                ->where('external_message_id', $externalMessageId)
                ->firstOrFail();
        }

        $messageId = $message->id ?: tap($message)->refresh()->id;

        DB::table('chats')
            ->where('id', $chat->id)
            ->update([
                'last_message_id' => $messageId,
                'last_message_at' => now(),
                'unread_count' => DB::raw('COALESCE(unread_count,0) + 1'),
                'updated_at' => now(),
            ]);

        $this->broadcastChatListEvent($chat, $message, $client, $externalChatId, $isNewChat || ! $hadMessagesBefore);

        $mediaGroupId = null;
        if (is_array($message->payload)) {
            $mediaGroupId = $message->payload['normalized']['media_group_id'] ?? null;
        }

        $media = (function () use ($message) {
            $mediaModels = $message->relationLoaded('media') ? $message->media : $message->media()->get();

            return $mediaModels->isNotEmpty()
                ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                : null;
        })();

        $messageDto = new MessageDto(
            id: (string) $message->id,
            chatId: (string) $chat->id,
            userId: (string) $client->id,
            direction: (string) ($message->direction ?? 'in'),
            type: (string) ($message->type ?? 'text'),
            status: $message->status instanceof MessageStatus
                ? $message->status
                : MessageStatus::from($message->status ?? MessageStatus::DELIVERED->value),
            text: (string) $message->text,
            mediaGroupId: $mediaGroupId,
            media: $media,
            createdAt: now()->toIso8601String(),
        );
        event(new ChatMessageCreated($messageDto));

        // Обработка воронки (если подключена) - запускаем Job
        $this->dispatchFunnelProcessing($integration, $chat, $message);
    }

    private function dispatchFunnelProcessing(Integration $integration, Chat $chat, Message $message): void
    {
        // Проверяем, подключена ли воронка к интеграции
        if (! $this->funnelService->hasFunnel($integration)) {
            return;
        }

        // Обрабатываем только чаты в авто-режиме
        if ($chat->status !== ChatStatus::AUTO->value) {
            return;
        }

        // Используем единый механизм буферизации/джоб для всех входящих сообщений
        $this->funnelService->bufferAndDispatch($integration, $chat, $message);
    }

    private function broadcastChatListEvent(Chat $chat, Message $message, Client $client, int $externalChatId, bool $isNewChat): void
    {
        try {
            $media = null;
            $mediaGroupId = null;
            if (is_array($message->payload)) {
                $mediaGroupId = $message->payload['normalized']['media_group_id'] ?? null;
            }
            $mediaModels = $message->relationLoaded('media') ? $message->media : $message->media()->get();
            if ($mediaModels->isNotEmpty()) {
                $media = array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all());
            }
            $lastMessageSummary = new MessageSummaryDto(
                id: (string) $message->id,
                type: (string) ($message->type ?? 'text'),
                text: (string) $message->text,
                mediaGroupId: $mediaGroupId,
                media: $media,
                createdAt: now()->toIso8601String(),
            );

            $clientName = (string) ($client->name ?? '');
            if ($clientName === '') {
                $clientName = 'Telegram User '.substr((string) $externalChatId, -4);
            }

            $avatar = $client->avatar ?? null;
            if ($avatar === '') {
                $avatar = null;
            }

            $clientShort = new ClientShortDto(
                id: (string) ($chat->client_id ?? $client->id ?? ''),
                name: $clientName,
                avatar: $avatar,
                tags: null,
            );

            $chatDto = new ChatListDto(
                id: (string) $chat->id,
                client: $clientShort,
                lastMessage: $lastMessageSummary,
                integrationName: $chat->integration->name,
                unreadCount: 1,
                lastMessageAt: now()->toIso8601String(),
                status: (string) ($chat->status ?? ChatStatus::MANUAL->value),
            );

            if ($isNewChat) {
                event(new ChatCreated($chatDto));
                Log::info('InboundMessageService.EVENT_ENQUEUED', [
                    'event' => 'ChatCreated',
                    'queue' => 'broadcasts',
                    'chat_id' => $chat->id,
                ]);
            } else {
                event(new ChatUpdated($chatDto));
                Log::info('InboundMessageService.EVENT_ENQUEUED', [
                    'event' => 'ChatUpdated',
                    'queue' => 'broadcasts',
                    'chat_id' => $chat->id,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('InboundMessageService.CHAT_LIST_EVENT_FAILED', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
