<?php

namespace App\DTO\Messaging\Outgoing;

use App\Enums\MediaType;
use App\Enums\Messaging\Outgoing\ParseMode;
use App\Enums\ServiceType;

/**
 * DTO для отправки медиа-сообщения.
 */
final readonly class MediaMessageDto
{
    /**
     * @param  ServiceType  $service  Тип сервиса
     * @param  string  $chatId  ID чата в канале
     * @param  MediaType  $type  Тип медиа (фото, видео, документ и т.д.)
     * @param  string  $file  Путь к файлу или URL
     * @param  string|null  $caption  Подпись к медиа
     * @param  ParseMode|null  $parseMode  Режим форматирования подписи
     * @param  int|null  $replyToMessageId  ID сообщения, на которое идёт ответ
     * @param  bool|null  $disableNotification  Отправлять без уведомления
     */
    public function __construct(
        public ServiceType $service,
        public string $chatId,
        public MediaType $type,
        public string $file,
        public ?string $caption = null,
        public ?ParseMode $parseMode = null,
        public ?int $replyToMessageId = null,
        public ?bool $disableNotification = null,
    ) {}
}
