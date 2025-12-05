<?php

namespace App\DTO\Messaging\Incoming;

use App\Enums\MediaType;

/**
 * DTO для входящего медиа-сообщения (универсально для Telegram, VK, WhatsApp, Email и т.д.)
 */
final readonly class IncomingMediaDto
{
    /**
     * @param  MediaType  $type  Тип медиа (фото, видео, документ и т.д.)
     * @param  string  $externalId  Идентификатор медиа в внешнем сервисе (file_id, media_id, guid)
     * @param  string|null  $mimeType  MIME-тип, если известен
     * @param  int|null  $duration  Длительность (для аудио, войса, видео)
     * @param  mixed|null  $thumbnail  Превью (строка, массив или объект — зависит от сервиса)
     * @param  mixed|null  $sizes  Размеры (массив вариантов фото, высота/ширина)
     * @param  string|null  $originalName  Исходное имя файла (если известно)
     * @param  string|null  $title  Название медиа (например, для аудио)
     * @param  string|null  $performer  Исполнитель (для аудио)
     */
    public function __construct(
        public MediaType $type,
        public string $externalId,
        public ?string $mimeType = null,
        public ?int $duration = null,
        public mixed $thumbnail = null,
        public mixed $sizes = null,
        public ?string $originalName = null,
        public ?string $title = null,
        public ?string $performer = null,
    ) {}
}
