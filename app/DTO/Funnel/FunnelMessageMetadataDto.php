<?php

namespace App\DTO\Funnel;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;

final readonly class FunnelMessageMetadataDto implements FromArrayInterface, ToArrayInterface
{
    public function __construct(
        public ?string $dify_message_id,
        public ?string $dify_conversation_id,
        public float $price,
        public int $sequence_index,
        public int $sequence_total,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            dify_message_id: $data['dify_message_id'] ?? null,
            dify_conversation_id: $data['dify_conversation_id'] ?? null,
            price: (float) ($data['price'] ?? 0),
            sequence_index: (int) ($data['sequence_index'] ?? 0),
            sequence_total: (int) ($data['sequence_total'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'dify_message_id' => $this->dify_message_id,
            'dify_conversation_id' => $this->dify_conversation_id,
            'price' => $this->price,
            'sequence_index' => $this->sequence_index,
            'sequence_total' => $this->sequence_total,
        ];
    }

    public function isFirst(): bool
    {
        return $this->sequence_index === 0;
    }

    public function isLast(): bool
    {
        return $this->sequence_index === $this->sequence_total - 1;
    }
}
