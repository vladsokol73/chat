<?php

namespace App\DTO\Messaging\Incoming;

use App\Enums\Messaging\Incoming\IncomingMessageSource;
use App\Enums\Messaging\Incoming\IncomingMessageType;
use App\Enums\ServiceType;
use DateTimeImmutable;

/**
 * Универсальное входящее сообщение из внешнего сервиса (Telegram, VK, WhatsApp и т.д.)
 * Используется для маппинга в Chat/Message модели.
 */
final readonly class IncomingMessageDto
{
    /**
     * @param  ServiceType  $service  Тип интеграции (telegram, vk, whatsapp ...)
     * @param  string  $externalChatId  ID чата во внешнем сервисе
     * @param  string  $externalMessageId  ID сообщения во внешнем сервисе
     * @param  string|null  $externalUserId  ID пользователя во внешнем сервисе
     * @param  IncomingMessageSource  $source  USER / BOT / SYSTEM
     * @param  IncomingMessageType  $type  TEXT / MEDIA
     * @param  string|null  $text  Текст сообщения или подпись
     * @param  IncomingMediaDto|null  $media  Нормализованная медиа-информация
     * @param  DateTimeImmutable|null  $sentAt  Время отправки сообщения
     * @param  array<string, mixed>|null  $raw  Сырой JSON апдейта
     */
    public function __construct(
        public ServiceType $service,
        public string $externalChatId,
        public string $externalMessageId,
        public ?string $externalUserId,
        public IncomingMessageSource $source,
        public IncomingMessageType $type,
        public ?string $text,
        public ?IncomingMediaDto $media = null,
        public ?DateTimeImmutable $sentAt = null,
        public ?array $raw = null,
    ) {}
}
