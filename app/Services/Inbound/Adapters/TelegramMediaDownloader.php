<?php

namespace App\Services\Inbound\Adapters;

use App\Contracts\Messaging\Media\MediaDownloaderInterface;
use App\DTO\Messaging\Incoming\IncomingMediaDto;
use App\Models\Integration;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use Throwable;

final class TelegramMediaDownloader implements MediaDownloaderInterface
{
    public function download(Integration $integration, IncomingMediaDto $media): string
    {
        try {
            // создаём бота под конкретную интеграцию
            $bot = new Nutgram($integration->token);

            // получаем мета-информацию о файле
            $file = $bot->getFile($media->externalId);

            // временный путь для сохранения
            $tmpPath = tempnam(sys_get_temp_dir(), 'tg_');

            // скачиваем файл
            $bot->downloadFile($file, $tmpPath);

            return $tmpPath;
        } catch (Throwable $e) {
            logger()->error('TelegramMediaDownloader:download', [
                'integrationId' => $integration->id,
                'fileId' => $media->externalId,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                sprintf('Failed to download Telegram file "%s": %s', $media->externalId, $e->getMessage()),
                previous: $e,
            );
        }
    }
}
