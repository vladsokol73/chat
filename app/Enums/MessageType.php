<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageType',
    description: 'Message type',
    type: 'string',
    enum: [
        'text',
        'photo',
        'video',
        'animation',
        'document',
        'audio',
        'voice',
        'video_note',
        'sticker',
        'funnel',
        'system',
    ]
)]
enum MessageType: string
{
    case TEXT = 'text';
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case ANIMATION = 'animation';
    case DOCUMENT = 'document';
    case AUDIO = 'audio';
    case VOICE = 'voice';
    case VIDEO_NOTE = 'video_note';
    case STICKER = 'sticker';
    case FUNNEL = 'funnel';
    case SYSTEM = 'system';
}
