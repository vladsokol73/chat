<?php

namespace App\DTO\CustomField;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CustomFieldDto',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'entityType', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'options', type: 'array', items: new OA\Items, nullable: true),
        new OA\Property(property: 'isRequired', type: 'boolean'),
        new OA\Property(property: 'integrationId', type: 'string', format: 'uuid', nullable: true),
    ],
    type: 'object'
)]
final readonly class CustomFieldDto implements FromArrayInterface, ToArrayInterface
{
    public function __construct(
        public string $id,
        public string $key,
        public string $name,
        public string $entityType,
        public string $type,
        public ?array $options,
        public bool $isRequired,
        public ?string $integrationId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) $data['id'],
            key: (string) $data['key'],
            name: (string) $data['name'],
            entityType: (string) $data['entityType'],
            type: (string) $data['type'],
            options: $data['options'] ?? null,
            isRequired: (bool) ($data['isRequired'] ?? false),
            integrationId: isset($data['integrationId']) ? (string) $data['integrationId'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'entityType' => $this->entityType,
            'type' => $this->type,
            'options' => $this->options,
            'isRequired' => $this->isRequired,
            'integrationId' => $this->integrationId,
        ];
    }
}
