<?php

namespace App\Enums\Messaging\Incoming;

enum IncomingMessageSource: string
{
    case USER = 'user';
    case BOT = 'bot';
    case SYSTEM = 'system';
}
