<?php

namespace App\Contracts\Outgoing;

use App\Models\Integration;
use App\Models\Message;

interface OutgoingMessageServiceInterface
{
    /**
     * Отправка текстового сообщения в интеграцию.
     *
     * @return array{message_id?: string|int|null}
     */
    public function send(Message $message, Integration $integration, array $payload): array;

    /**
     * Отправка "печатает..." (chat action).
     */
    public function sendTyping(Integration $integration, string $chatId): void;
}
