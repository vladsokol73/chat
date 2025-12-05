<?php

namespace App\Services\Inbound;

use App\Contracts\Messaging\Media\MediaDownloaderInterface;
use App\Enums\ServiceType;
use App\Models\Integration;
use App\Services\Inbound\Adapters\TelegramMediaDownloader;
use InvalidArgumentException;

/**
 * Роутер для выбора сервиса загрузки медиа по типу интеграции.
 */
class MediaRouter
{
    public function resolve(Integration $integration): MediaDownloaderInterface
    {
        $service = $integration->service->value;

        return match ($service) {
            ServiceType::TELEGRAM->value => app(TelegramMediaDownloader::class),

            default => throw new InvalidArgumentException(
                sprintf('Unsupported media downloader for service "%s".', $service)
            ),
        };
    }
}
