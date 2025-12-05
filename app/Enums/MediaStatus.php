<?php

namespace App\Enums;

/**
 * Статус медиа-файла.
 */
enum MediaStatus: string
{
    case PENDING = 'pending';
    case ATTACHED = 'attached';
    case EXPIRED = 'expired';
}
