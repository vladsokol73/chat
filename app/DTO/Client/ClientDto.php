<?php

namespace App\DTO\Client;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClientDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: 'b1a4b9f2-3c6d-4e8f-9a1b-2c3d4e5f6a7b'),
        new OA\Property(property: 'name', type: 'string', example: 'John Week'),
        new OA\Property(
            property: 'avatar',
            type: 'string',
            example: 'https://wallpapers.com/images/hd/blue-cat-eye-cute-british-shorthair-berwrnysmiqby7j0.jpg',
            nullable: true
        ),
        new OA\Property(property: 'phone', type: 'string', example: '88005553535'),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TagDto'),
            example: [
                ['id' => 't1', 'name' => 'vip', 'color' => '#FF9900'],
                ['id' => 't2', 'name' => 'new'],
            ],
            nullable: true
        ),
        new OA\Property(property: 'comment', type: 'string', example: 'Example of comment', nullable: true),
    ]
)]
final readonly class ClientDto implements FromArrayInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $avatar,
        public string $phone,
        /** @var null|TagDto[] */
        public ?array $tags,
        public ?string $comment,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            avatar: $data['avatar'] ?? null,
            phone: (string) $data['phone'],
            tags: isset($data['tags'])
                ? array_map(static fn (array $tag) => TagDto::fromArray($tag), $data['tags'])
                : null,
            comment: $data['comment'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'tags' => $this->tags === null
                ? null
                : array_map(static fn (TagDto $t) => $t->toArray(), $this->tags),
            'comment' => $this->comment,
        ];
    }
}
