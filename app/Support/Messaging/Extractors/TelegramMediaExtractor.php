<?php

namespace App\Support\Messaging\Extractors;

use App\Contracts\Messaging\MediaExtractorInterface;
use App\DTO\Messaging\Incoming\IncomingMediaDto;
use App\Enums\MediaType;
use SergiX44\Nutgram\Telegram\Types\Message\Message;

class TelegramMediaExtractor implements MediaExtractorInterface
{
    public function extract(Message $message): ?IncomingMediaDto
    {
        if ($message->photo) {
            $photo = $message->photo[count($message->photo) - 1];

            return new IncomingMediaDto(
                type: MediaType::PHOTO,
                externalId: $photo->file_id,
                mimeType: 'image/jpeg',
                sizes: $message->photo,
            );
        }

        if ($message->video) {
            return new IncomingMediaDto(
                type: MediaType::VIDEO,
                externalId: $message->video->file_id,
                mimeType: $message->video->mime_type ?? null,
                duration: $message->video->duration ?? null,
                thumbnail: $message->video->thumbnail ?? null,
            );
        }

        if ($message->document) {
            return new IncomingMediaDto(
                type: MediaType::DOCUMENT,
                externalId: $message->document->file_id,
                mimeType: $message->document->mime_type ?? null,
                originalName: $message->document->file_name ?? null,
            );
        }

        if ($message->voice) {
            return new IncomingMediaDto(
                type: MediaType::VOICE,
                externalId: $message->voice->file_id,
                mimeType: $message->voice->mime_type ?? null,
                duration: $message->voice->duration ?? null,
            );
        }

        if ($message->audio) {
            return new IncomingMediaDto(
                type: MediaType::AUDIO,
                externalId: $message->audio->file_id,
                mimeType: $message->audio->mime_type ?? null,
                duration: $message->audio->duration ?? null,
                title: $message->audio->title ?? null,
                performer: $message->audio->performer ?? null,
            );
        }

        if ($message->video_note) {
            return new IncomingMediaDto(
                type: MediaType::VIDEO_NOTE,
                externalId: $message->video_note->file_id,
                duration: $message->video_note->duration ?? null,
                thumbnail: $message->video_note->thumbnail ?? null,
            );
        }

        if ($message->sticker) {
            return new IncomingMediaDto(
                type: MediaType::STICKER,
                externalId: $message->sticker->file_id,
                thumbnail: $message->sticker->thumbnail ?? null,
            );
        }

        return null;
    }
}
