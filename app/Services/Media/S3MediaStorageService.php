<?php

namespace App\Services\Media;

use App\Contracts\Media\MediaStorage;
use App\Support\Media\MediaExtensionResolver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class S3MediaStorageService implements MediaStorage
{
    public function __construct(
        private MediaExtensionResolver $extensionResolver,
    ) {}

    public function store(string $prefix, ?string $mimeType, string $tmpPath): string
    {
        $extension = $this->extensionResolver->resolve($mimeType);

        $path = sprintf(
            '%s/%s.%s',
            trim($prefix, '/'),
            Str::uuid()->toString(),
            $extension,
        );

        Storage::disk('s3')->put(
            $path,
            file_get_contents($tmpPath),
            [
                'visibility' => 'public',
                'ContentType' => $mimeType,
            ],
        );

        return $path;
    }
}
