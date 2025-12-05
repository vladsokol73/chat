<?php

namespace App\Services\Dify;

use App\DTO\Dify\DifyFileDto;
use App\Enums\Dify\DifyFileType;
use App\Enums\Dify\DifyTransferMethod;
use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

final readonly class DifyMediaConverter
{
    /**
     * Convert Media model to DifyFileDto
     */
    public function convert(Media $media): ?DifyFileDto
    {
        if (! $media->path) {
            return null;
        }

        $url = Storage::disk('s3')->url($media->path);
        if (! $url) {
            return null;
        }

        $fileType = $this->mapMediaTypeToDifyFileType($media->type);

        return new DifyFileDto(
            type: $fileType,
            transfer_method: DifyTransferMethod::REMOTE_URL,
            url: $url
        );
    }

    /**
     * Map MediaType to DifyFileType
     */
    private function mapMediaTypeToDifyFileType(MediaType $mediaType): DifyFileType
    {
        return match ($mediaType) {
            MediaType::PHOTO => DifyFileType::IMAGE,
            MediaType::VIDEO => DifyFileType::VIDEO,
            MediaType::AUDIO, MediaType::VOICE => DifyFileType::AUDIO,
            MediaType::DOCUMENT => DifyFileType::DOCUMENT,
            default => DifyFileType::CUSTOM,
        };
    }
}
