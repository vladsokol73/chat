<?php

namespace App\Enums\Dify;

enum DifyMessageFormat: string
{
    case SIMPLE = 'simple_text';
    case JSON_STRING = 'json_string';
    case ARRAY_MESSAGES = 'messages_array';
}
