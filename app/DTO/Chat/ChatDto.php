<?php

namespace App\DTO\Chat;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\DTO\Client\ClientDto;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '0f2c7b3a-0e97-4a2f-9a1d-9b2b6e3e9a1c'),
        new OA\Property(property: 'client', ref: '#/components/schemas/ClientDto'),
        new OA\Property(
            property: 'messages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MessageDto')
        ),
    ]
)]
final readonly class ChatDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public ClientDto $client,
        public array $messages,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) $data['id'],
            client: $data['client'],
            messages: $data['messages'],
        );
    }

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Chat);

        return new static(
            id: $model->id,
            client: new ClientDto(
                id: (string) ($model->client->id ?? ''),
                name: (string) ($model->client->name ?? ''),
                avatar: $model->client->avatar ?? null,
                phone: (string) ($model->client->phone ?? ''),
                tags: null,
                comment: $model->client->comment ?? null,
            ),
            messages: ($model->messages instanceof Collection)
                            ? $model->messages->map(fn ($m) => MessageDto::fromModel($m))->all()
                          : [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client' => $this->client->toArray(),
            'messages' => array_map(fn (MessageDto $m) => $m->toArray(), $this->messages),
        ];
    }
}
