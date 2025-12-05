<?php

namespace App\Support;

use Throwable;

final class TokenMaskHelper
{
    /**
     * Возвращает замаскированный токен (например: 123*****abc)
     */
    public static function mask(?string $token): ?string
    {
        if (empty($token)) {
            return null;
        }

        try {
            // Минимальная длина для корректной маски
            if (strlen($token) <= 6) {
                return str_repeat('*', strlen($token));
            }

            return substr($token, 0, 3).'*****'.substr($token, -3);
        } catch (Throwable) {
            return null;
        }
    }
}
