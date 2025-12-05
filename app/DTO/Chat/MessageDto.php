<?php

namespace App\DTO\Chat;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '9f3a2b1c-4d5e-6f7a-8b9c-0d1e2f3a4b5c'),
        new OA\Property(property: 'chat_id', type: 'string', format: 'uuid', example: '0f2c7b3a-0e97-4a2f-9a1d-9b2b6e3e9a1c'),
        new OA\Property(property: 'user_id', type: 'string', format: 'uuid', example: '1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d', nullable: true),
        new OA\Property(property: 'direction', description: 'Направление сообщения: in (входящее), out (исходящее)', type: 'string', enum: ['in', 'out'], example: 'in'),
        new OA\Property(property: 'type', ref: '#/components/schemas/MessageType'),
        new OA\Property(property: 'status', ref: '#/components/schemas/MessageStatus'),
        new OA\Property(property: 'text', type: 'string', example: 'This is a generated mock message.'),
        new OA\Property(property: 'media_group_id', type: 'string', nullable: true),
        new OA\Property(
            property: 'media',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MessageMediaDto'),
            nullable: true,
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-09-23T10:01:00+00:00'),
    ]
)]
final readonly class MessageDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public string $chatId,
        public ?string $userId,
        public MessageDirection $direction,
        public MessageType $type,
        public MessageStatus $status,
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
            chatId: (string) $data['chatId'],
            userId: (string) ($data['userId'] ?? $data['user_id']),
            direction: MessageDirection::from($data['direction'] ?? 'in'),
            type: MessageType::from($data['type'] ?? 'text'),
            status: MessageStatus::from($data['status'] ?? 'queued'),
            text: (string) $data['text'],
            mediaGroupId: $data['mediaGroupId'] ?? $data['media_group_id'] ?? null,
            media: (isset($data['media']) && is_array($data['media']))
                ? array_values(array_filter(array_map(
                    static fn ($item) => is_array($item) ? MessageMediaDto::fromArray($item) : null,
                    $data['media']
                )))
                : null,
            createdAt: (string) ($data['createdAt'] ?? $data['created_at'] ?? ''),
        );
    }

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Message);

        return new static(
            id: (string) $model->id,
            chatId: (string) $model->chat_id,
            userId: $model->user_id ? (string) $model->user_id : null,
            direction: $model->direction instanceof MessageDirection
                ? $model->direction
                : MessageDirection::from($model->direction),
            type: $model->type instanceof MessageType
                ? $model->type
                : MessageType::from($model->type),
            status: $model->status instanceof MessageStatus
                ? $model->status
                : MessageStatus::from($model->status),
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
            'chat_id' => $this->chatId,
            'user_id' => $this->userId,
            'direction' => $this->direction,
            'type' => $this->type,
            'status' => $this->status->value,
            'text' => $this->text,
            'media_group_id' => $this->mediaGroupId,
            'media' => $this->media ? array_map(static fn (MessageMediaDto $m) => $m->toArray(), $this->media) : null,
            'created_at' => $this->createdAt,
        ];
    }
}
