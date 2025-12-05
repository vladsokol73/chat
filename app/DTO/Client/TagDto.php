<?php

namespace App\DTO\Client;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TagDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'b8f3a1c2-1234-4a5b-9cde-112233445566'),
        new OA\Property(property: 'name', type: 'string', example: 'vip'),
        new OA\Property(property: 'color', type: 'string', example: '#FF9900', nullable: true),
    ]
)]
final readonly class TagDto implements FromArrayInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $color = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            color: $data['color'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
        ];
    }
}
