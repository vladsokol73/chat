<?php

namespace App\DTO\Client;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;
use Illuminate\Http\UploadedFile;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateClientDto',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'phone', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'avatar', type: 'string', format: 'binary', nullable: true, description: 'Avatar file (image)'),
        new OA\Property(property: 'comment', type: 'string', maxLength: 512, nullable: true),
    ]
)]
final readonly class UpdateClientDto implements FromArrayInterface, ToArrayInterface
{
    public function __construct(
        public ?string $name,
        public ?string $phone,
        public ?UploadedFile $avatar,
        public ?string $comment,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            comment: $data['comment'] ?? null,
        );
    }

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return new self(
            name: $request->input('name'),
            phone: $request->input('phone'),
            avatar: $request->file('avatar'),
            comment: $request->input('comment'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'avatar' => $this->avatar?->getClientOriginalName(),
            'comment' => $this->comment,
        ];
    }
}
