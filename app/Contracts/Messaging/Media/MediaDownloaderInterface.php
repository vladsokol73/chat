<?php

namespace App\Contracts\Messaging\Media;

use App\DTO\Messaging\Incoming\IncomingMediaDto;
use App\Models\Integration;

interface MediaDownloaderInterface
{
    /**
     * Скачать медиа из внешнего сервиса во временный файл.
     *
     * @return string Путь к временному файлу
     */
    public function download(Integration $integration, IncomingMediaDto $media): string;
}
