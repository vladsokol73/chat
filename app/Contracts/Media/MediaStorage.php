<?php

namespace App\Contracts\Media;

/**
 * Контракт для сервисов хранения медиа в объектном хранилище.
 */
interface MediaStorage
{
    public function store(string $prefix, ?string $mimeType, string $tmpPath): string;
}
