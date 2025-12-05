<?php

namespace App\DTO\Messaging\Outgoing;

use App\Enums\Messaging\Outgoing\ParseMode;
use App\Enums\ServiceType;

/**
 * DTO для отправки текстового сообщения во внешний канал.
 */
final readonly class TextMessageDto
{
    /**
     * @param  ServiceType  $service  Тип сервиса
     * @param  string  $chatId  Идентификатор чата в канале
     * @param  string  $text  Текст сообщения
     * @param  ParseMode|null  $parseMode  Режим парсинга текста (HTML/Markdown)
     * @param  int|null  $replyToMessageId  ID сообщения, на которое идёт ответ
     * @param  bool|null  $disableWebPagePreview  Отключить предпросмотр ссылок
     * @param  bool|null  $disableNotification  Не присылать пользователю уведомление
     */
    public function __construct(
        public ServiceType $service,
        public string $chatId,
        public string $text,
        public ?ParseMode $parseMode = null,
        public ?int $replyToMessageId = null,
        public ?bool $disableWebPagePreview = null,
        public ?bool $disableNotification = null,
    ) {}
}
