<?php

namespace App\DTO\Chat;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\Enums\MessageType;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageSummaryDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: 'c7d9f3c2-6a0e-4d7a-8f7a-1b2e3c4d5e6f'),
        new OA\Property(property: 'type', ref: '#/components/schemas/MessageType'),
        new OA\Property(property: 'text', type: 'string', example: 'Hello, this is a mock message!'),
        new OA\Property(property: 'media_group_id', type: 'string', nullable: true),
        new OA\Property(
            property: 'media',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MessageMediaDto'),
            nullable: true,
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-09-23T10:00:00+00:00'),
    ]
)]
final readonly class MessageSummaryDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public MessageType $type,
        public string $text,
        public ?string $mediaGroupId,
        /** @var list<MessageMediaDto>|null */
        public ?array $media,
        public string $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) $data['id'],
            type: $data['type'] instanceof MessageType
                ? $data['type']
                : MessageType::from($data['type'] ?? 'text'),
            text: (string) $data['text'],
            mediaGroupId: $data['mediaGroupId'] ?? $data['media_group_id'] ?? null,
            media: (isset($data['media']) && is_array($data['media']))
                ? array_values(array_filter(array_map(
                    static fn ($item) => is_array($item) ? MessageMediaDto::fromArray($item) : null,
                    $data['media']
                )))
                : null,
            createdAt: (string) ($data['created_at'] ?? $data['createdAt'] ?? ''),
        );
    }

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof Message) {
            throw new InvalidArgumentException('Expected Message model');
        }

        return new static(
            id: (string) $model->id,
            type: $model->type instanceof MessageType
                ? $model->type
                : MessageType::from($model->type),
            text: (string) $model->text,
            mediaGroupId: $model->media_group_id ?? null,
            media: (function () use ($model) {
                $mediaModels = $model->relationLoaded('media') ? $model->media : $model->media()->get();

                return $mediaModels->isNotEmpty()
                    ? array_values($mediaModels->map(static fn ($m) => MessageMediaDto::fromModel($m))->all())
                    : null;
            })(),
            createdAt: $model->created_at?->toISOString() ?? (string) $model->created_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'text' => $this->text,
            'media_group_id' => $this->mediaGroupId,
            'media' => $this->media ? array_map(static fn (MessageMediaDto $m) => $m->toArray(), $this->media) : null,
            'created_at' => $this->createdAt,
        ];
    }
}
