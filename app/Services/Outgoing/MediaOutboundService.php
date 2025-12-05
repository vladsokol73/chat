<?php

namespace App\Services\Outgoing;

use App\Contracts\Media\MediaStorage;
use App\DTO\Chat\MessageMediaDto;
use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

// Сервис для работы с исходящими медиа.
final readonly class MediaOutboundService
{
    public function __construct(
        private MediaStorage $mediaStorage,
    ) {}

    public function uploadFile(
        UploadedFile $file,
        ?MediaType $forcedType = null,
    ): MessageMediaDto {
        $mimeType = $file->getMimeType() ?: null;

        $type = $forcedType ?? MediaType::fromMime($mimeType);

        $prefix = sprintf(
            'outgoing/%s',
            strtolower($type->value),
        );

        $path = $this->mediaStorage->store(
            $prefix,
            $mimeType,
            $file->getRealPath(),
        );

        /** @var Media $media */
        $media = Media::query()->create([
            'message_id' => null,
            'type' => $type,
            'external_id' => null,
            'mime_type' => $mimeType,
            'duration' => null,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'sizes' => null,
            'thumbnail' => null,
            'title' => null,
            'performer' => null,
            'status' => MediaStatus::PENDING,
        ]);

        return MessageMediaDto::fromModel($media);
    }

    /**
     * @throws Throwable
     */
    public function attachToMessage(Message $message, array $mediaIds): array
    {
        if ($mediaIds === []) {
            return [];
        }

        return DB::transaction(function () use ($message, $mediaIds): array {
            $medias = Media::query()
                ->whereIn('id', $mediaIds)
                ->whereNull('message_id')
                ->where('status', MediaStatus::PENDING)
                ->lockForUpdate()
                ->get();

            if ($medias->count() !== count($mediaIds)) {
                throw new RuntimeException('Some media cannot be attached (already used or not found).');
            }

            $dtos = [];

            foreach ($medias as $media) {
                $media->update([
                    'message_id' => $message->id,
                    'status' => MediaStatus::ATTACHED,
                ]);

                $dtos[] = MessageMediaDto::fromModel($media);
            }

            return $dtos;
        });
    }
}
