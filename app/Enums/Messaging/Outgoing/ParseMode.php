<?php

namespace App\Enums\Messaging\Outgoing;

/**
 * Режим форматирования текста при отправке сообщения.
 * Соответствует Telegram API.
 */
enum ParseMode: string
{
    case HTML = 'HTML';
    case MARKDOWN = 'Markdown';
}
