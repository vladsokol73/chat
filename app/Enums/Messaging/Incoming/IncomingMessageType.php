<?php

namespace App\Enums\Messaging\Incoming;

enum IncomingMessageType: string
{
    case TEXT = 'text';
    case MEDIA = 'media';
}
