<?php

namespace App\Services\Outgoing;

use App\DTO\Chat\MessageMediaDto;
use App\Enums\MediaType;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class OutgoingMediaService
{
    /**
     * Upload media from URL to S3 and create Media record
     */
    public function handle(Message $message, MessageMediaDto $mediaDto): void
    {
        if (! $mediaDto->publicUrl) {
            throw new RuntimeException('Media public_url is required');
        }

        // Download file from URL
        $response = Http::timeout(30)->get($mediaDto->publicUrl);
        if (! $response->successful()) {
            throw new RuntimeException("Failed to download media from URL: {$mediaDto->publicUrl}");
        }

        $fileContent = $response->body();
        $mimeType = $response->header('Content-Type') ?? $mediaDto->mime ?? 'application/octet-stream';

        // Upload to S3
        $s3Path = $this->uploadToS3($mediaDto, $fileContent, $mimeType);

        // Create Media record
        $message->media()->create([
            'type' => MediaType::from($mediaDto->kind),
            'external_id' => $mediaDto->fileId ?? uniqid('outgoing_', true),
            'mime_type' => $mimeType,
            'duration' => $mediaDto->duration,
            'path' => $s3Path,
            'original_name' => $mediaDto->fileName,
            'thumbnail' => $mediaDto->thumbnail,
            'sizes' => $this->buildSizesArray($mediaDto),
            'title' => null,
            'performer' => null,
        ]);
    }

    private function uploadToS3(MessageMediaDto $mediaDto, string $fileContent, string $mimeType): string
    {
        $extension = $this->resolveExtension($mimeType, $mediaDto->fileName);

        $path = sprintf(
            'outgoing/%s/%s.%s',
            strtolower($mediaDto->kind),
            uniqid('', true),
            $extension,
        );

        Storage::disk('s3')->put(
            $path,
            $fileContent,
            [
                'visibility' => 'public',
                'ContentType' => $mimeType,
            ],
        );

        return $path;
    }

    private function resolveExtension(string $mimeType, ?string $fileName): string
    {
        // Try to get extension from filename
        if ($fileName) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if ($ext) {
                return strtolower($ext);
            }
        }

        // Fallback to mime type
        return match (true) {
            str_contains($mimeType, 'jpeg') => 'jpg',
            str_contains($mimeType, 'png') => 'png',
            str_contains($mimeType, 'gif') => 'gif',
            str_contains($mimeType, 'webp') => 'webp',
            str_contains($mimeType, 'mp4') => 'mp4',
            str_contains($mimeType, 'webm') => 'webm',
            str_contains($mimeType, 'ogg') => 'oga',
            str_contains($mimeType, 'audio') => 'mp3',
            str_contains($mimeType, 'pdf') => 'pdf',
            str_contains($mimeType, 'document') => 'doc',
            default => 'bin',
        };
    }

    private function buildSizesArray(MessageMediaDto $mediaDto): ?array
    {
        if ($mediaDto->width === null && $mediaDto->height === null) {
            return null;
        }

        return array_filter([
            'width' => $mediaDto->width,
            'height' => $mediaDto->height,
        ]);
    }
}
