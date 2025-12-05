<?php

namespace App\DTO\Dify;

use App\Contracts\DTO\FromArrayInterface;

final readonly class DifyMessageChunkDto implements FromArrayInterface
{
    public function __construct(
        public string $text,
        public ?string $type = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            text: trim((string) ($data['text'] ?? '')),
            type: $data['type'] ?? null,
        );
    }
}
