<?php

namespace App\DTO\Integration;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\Enums\ServiceType;
use App\Models\Integration;
use App\Support\TokenMaskHelper;

use function assert;

use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'IntegrationDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '7b6f1a2b-3c4d-5e6f-7a8b-9c0d1e2f3a4b', nullable: true),
        new OA\Property(property: 'name', type: 'string', example: 'Main Telegram Bot'),
        new OA\Property(property: 'service', ref: '#/components/schemas/ServiceType'),
        new OA\Property(property: 'token', type: 'string', example: '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11', nullable: true),
        new OA\Property(property: 'token_mask', type: 'string', example: '123*****abc', nullable: true),
    ]
)]
final readonly class IntegrationDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public ?string $id,
        public string $name,
        public ServiceType $service,
        public ?string $token = null,
        public ?string $token_mask = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            service: $data['service'],
            token: $data['token'] ?? null,
        );
    }

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Integration);

        $token = $model->token;

        $mask = TokenMaskHelper::mask($token);

        return new static(
            id: (string) $model->id,
            name: (string) $model->name,
            service: $model->service instanceof ServiceType
                ? $model->service
                : ServiceType::from($model->service),
            token_mask: $mask,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'service' => $this->service,
            'token' => $this->token,
        ];
    }
}
