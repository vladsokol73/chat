<?php

namespace App\Enums;

/**
 * Тип медиа-сообщения.
 */
enum MediaType: string
{
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case VOICE = 'voice';
    case VIDEO_NOTE = 'video_note';
    case STICKER = 'sticker';

    /**
     * Определить тип медиа по MIME-типу.
     */
    public static function fromMime(?string $mimeType): self
    {
        $mimeType = strtolower($mimeType ?? '');

        return match (true) {
            str_starts_with($mimeType, 'image/') => self::PHOTO,
            str_starts_with($mimeType, 'video/') => self::VIDEO,
            str_starts_with($mimeType, 'audio/') => self::AUDIO,

            default => self::DOCUMENT,
        };
    }
}
