<?php

namespace App\DTO\Messaging\Outgoing;

use App\Enums\Messaging\Outgoing\ChatActionType;
use App\Enums\ServiceType;

/**
 * DTO для отправки действия в чат (typing, upload_photo и т.п.).
 */
final readonly class ChatActionDto
{
    /**
     * @param  ServiceType  $service  Тип сервиса
     * @param  string  $chatId  ID чата
     * @param  ChatActionType  $action  Тип действия (typing, upload_photo и др.)
     */
    public function __construct(
        public ServiceType $service,
        public string $chatId,
        public ChatActionType $action = ChatActionType::TYPING,
    ) {}
}
