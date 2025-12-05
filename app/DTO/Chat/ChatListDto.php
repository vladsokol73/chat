<?php

namespace App\DTO\Chat;

use App\Contracts\DTO\FromCollectionInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\DTO\Client\ClientShortDto;
use App\Enums\ChatStatus;
use App\Enums\MessageType;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatListDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '0f2c7b3a-0e97-4a2f-9a1d-9b2b6e3e9a1c'),
        new OA\Property(property: 'client', ref: '#/components/schemas/ClientShortDto'),
        new OA\Property(property: 'last_message', ref: '#/components/schemas/MessageSummaryDto'),
        new OA\Property(property: 'integration_name', type: 'string', example: 'Telegram'),
        new OA\Property(property: 'unread_count', type: 'integer', example: 3),
        new OA\Property(property: 'last_message_at', type: 'string', format: 'date-time', example: '2025-09-23T10:00:00+00:00'),
        new OA\Property(property: 'status', ref: '#/components/schemas/ChatStatus'),
    ]
)]
final readonly class ChatListDto implements FromCollectionInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public ClientShortDto $client,
        public MessageSummaryDto $lastMessage,
        public string $integrationName,
        public int $unreadCount = 0,
        public string $lastMessageAt = '',
        public string $status = ChatStatus::MANUAL->value,
    ) {}

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Chat);

        $clientModel = $model->client;
        $clientShort = new ClientShortDto(
            id: (string) ($clientModel->id ?? ''),
            name: (string) ($clientModel->name ?? 'Unknown'),
            avatar: $clientModel->avatar ?? null,
            tags: null,
        );

        $lastModel = $model->messages()->latest('created_at')->first();

        $lastSummary = new MessageSummaryDto(
            id: (string) ($lastModel->id ?? ''),
            type: $lastModel->type ?? MessageType::TEXT,
            text: (string) ($lastModel->text ?? ''),
            mediaGroupId: null,
            media: null,
            createdAt: now()->toIso8601String(),
        );

        return new self(
            id: (string) $model->id,
            client: $clientShort,
            lastMessage: $lastSummary,
            integrationName: (string) $model->integration->name,
            unreadCount: (int) ($model->unread_count ?? 0),
            lastMessageAt: (string) ($model->last_message_at ?? ''),
            status: (string) ($model->status ?? ChatStatus::MANUAL->value),
        );
    }

    public static function fromCollection(Collection $collection): array
    {
        return $collection->map(fn ($item) => static::fromModel($item))->toArray();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client' => $this->client->toArray(),
            'last_message' => $this->lastMessage->toArray(),
            'integration_name' => $this->integrationName,
            'unread_count' => $this->unreadCount,
            'last_message_at' => $this->lastMessageAt,
            'status' => $this->status,
        ];
    }
}
