<?php

namespace App\Services\Inbound;

use App\Contracts\Media\MediaStorage;
use App\DTO\Messaging\Incoming\IncomingMediaDto;
use App\Models\Integration;
use App\Models\Message;

final readonly class MediaInboundService
{
    public function __construct(
        private MediaRouter $router,
        private MediaStorage $mediaStorage,
    ) {}

    public function handle(Integration $integration, Message $message, IncomingMediaDto $media): void
    {
        $downloader = $this->router->resolve($integration);

        $tmpPath = $downloader->download($integration, $media);

        $prefix = sprintf(
            'incoming/%s',
            strtolower($media->type->value),
        );

        $s3Path = $this->mediaStorage->store($prefix, $media->mimeType, $tmpPath);

        $message->media()->create([
            'type' => $media->type,
            'external_id' => $media->externalId,
            'mime_type' => $media->mimeType,
            'duration' => $media->duration,
            'path' => $s3Path,
            'original_name' => $media->originalName,
            'thumbnail' => $media->thumbnail,
            'sizes' => $media->sizes,
            'title' => $media->title,
            'performer' => $media->performer,
        ]);

        @unlink($tmpPath);
    }
}
