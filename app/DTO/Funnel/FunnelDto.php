<?php

namespace App\DTO\Funnel;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\FromModelInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\Models\Funnel;
use App\Support\TokenMaskHelper;

use function assert;

use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FunnelDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '7b6f1a2b-3c4d-5e6f-7a8b-9c0d1e2f3a4b', nullable: true),
        new OA\Property(property: 'name', type: 'string', example: 'Welcome Funnel'),
        new OA\Property(property: 'integration_id', type: 'string', example: '7b6f1a2b-3c4d-5e6f-7a8b-9c0d1e2f3a4b'),
        new OA\Property(property: 'api_key', type: 'string', example: 'app-SyO70Aa9LA0p5VNtSmuH88Ib', nullable: true),
        new OA\Property(property: 'api_key_mask', type: 'string', example: 'app-SyO*****H88Ib', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-06T12:00:00Z', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-11-06T12:00:00Z', nullable: true),
    ]
)]
final readonly class FunnelDto implements FromArrayInterface, FromModelInterface, ToArrayInterface
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $integration_id,
        public ?string $api_key = null,
        public ?string $api_key_mask = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            integration_id: $data['integration_id'],
            api_key: $data['api_key'] ?? null,
        );
    }

    public static function fromModel(Model $model): static
    {
        assert($model instanceof Funnel);

        $apiKey = $model->api_key;
        $mask = TokenMaskHelper::mask($apiKey);

        return new static(
            id: (string) $model->id,
            name: (string) $model->name,
            integration_id: (string) $model->integration_id,
            api_key_mask: $mask,
            created_at: $model->created_at?->toIso8601String(),
            updated_at: $model->updated_at?->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'integration_id' => $this->integration_id,
            'api_key' => $this->api_key,
        ];
    }
}
