<?php

namespace App\DTO\Chat;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageMediaDto',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'kind', type: 'string'),
        new OA\Property(property: 'file_id', type: 'string', nullable: true),
        new OA\Property(property: 'file_unique_id', type: 'string', nullable: true),
        new OA\Property(property: 'width', type: 'integer', nullable: true),
        new OA\Property(property: 'height', type: 'integer', nullable: true),
        new OA\Property(property: 'duration', type: 'integer', nullable: true),
        new OA\Property(property: 'mime', type: 'string', nullable: true),
        new OA\Property(property: 'file_name', type: 'string', nullable: true),
        new OA\Property(property: 'thumbnail', nullable: true),
        new OA\Property(property: 'public_url', type: 'string', nullable: true),
    ]
)]
final readonly class MessageMediaDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public string $kind,
        public ?string $fileId,
        public ?string $fileUniqueId,
        public ?int $width,
        public ?int $height,
        public ?int $duration,
        public ?string $mime,
        public ?string $fileName,
        public mixed $thumbnail,
        public ?string $publicUrl,
    ) {}

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Media);

        $sizes = is_array($model->sizes) ? $model->sizes : [];

        return new self(
            id: $model->id,
            kind: $model->type->value,
            fileId: $model->external_id,
            fileUniqueId: null,
            width: $sizes['width'] ?? null,
            height: $sizes['height'] ?? null,
            duration: $model->duration,
            mime: $model->mime_type,
            fileName: $model->original_name,
            thumbnail: $model->thumbnail,
            publicUrl: $model->path
                ? Storage::disk('s3')->url($model->path)
                : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            kind: (string) ($data['kind'] ?? ''),
            fileId: $data['file_id'] ?? null,
            fileUniqueId: $data['file_unique_id'] ?? null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            mime: $data['mime'] ?? null,
            fileName: $data['file_name'] ?? null,
            thumbnail: $data['thumbnail'] ?? null,
            publicUrl: $data['public_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'file_id' => $this->fileId,
            'file_unique_id' => $this->fileUniqueId,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'mime' => $this->mime,
            'file_name' => $this->fileName,
            'thumbnail' => $this->thumbnail,
            'public_url' => $this->publicUrl,
        ];
    }
}
