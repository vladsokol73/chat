<?php

namespace App\Contracts\Messaging;

use App\DTO\Messaging\Incoming\IncomingMediaDto;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

/**
 * Контракт для всех media extractors (Telegram, WhatsApp, etc.)
 */
interface MediaExtractorInterface
{
    /**
     * Возвращает нормализованное медиа или null.
     */
    public function extract(Message $message): ?IncomingMediaDto;
}
