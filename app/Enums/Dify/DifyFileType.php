<?php

namespace App\Enums\Dify;

enum DifyFileType: string
{
    case DOCUMENT = 'document';
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case CUSTOM = 'custom';
}
