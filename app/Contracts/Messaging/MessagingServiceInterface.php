<?php

namespace App\Contracts\Messaging;

use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\DTO\Messaging\Outgoing\ChatActionDto;
use App\DTO\Messaging\Outgoing\MediaMessageDto;
use App\DTO\Messaging\Outgoing\TextMessageDto;
use App\Models\Integration;
use Illuminate\Http\Request;

interface MessagingServiceInterface
{
    /**
     * Отправка текстового сообщения.
     *
     * @return array<string, mixed> Данные, вернувшиеся от внешнего API.
     */
    public function sendTextMessage(Integration $integration, TextMessageDto $dto): array;

    /**
     * Отправка медиа-сообщения.
     *
     * @return array<string, mixed>
     */
    public function sendMediaMessage(Integration $integration, MediaMessageDto $dto): array;

    /**
     * Отправка действия в чат (typing и т.п.).
     */
    public function sendChatAction(Integration $integration, ChatActionDto $dto): bool;

    /**
     * Обработка входящего webhook.
     * Должна валидировать update, маппить в IncomingMessageDto и передавать в pipeline.
     */
    public function handleWebhook(Integration $integration, Request $request): ?IncomingMessageDto;

    /**
     * Регистрация webhook в канале, если требуется.
     * Например Telegram setWebhook, WhatsApp verify challenge и т.д.
     */
    public function setupWebhook(Integration $integration): void;

    /**
     * Проверка валидности токена/ключа интеграции.
     */
    public function validateIntegration(Integration $integration): bool;
}
