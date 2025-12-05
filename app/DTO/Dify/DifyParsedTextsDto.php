<?php

namespace App\DTO\Dify;

use App\Contracts\DTO\ToArrayInterface;

final readonly class DifyParsedTextsDto implements ToArrayInterface
{
    /**
     * @param  array<int, string>  $texts
     */
    public function __construct(
        public array $texts,
        public string $conversation_id,
        public float $total_price,
        public ?string $message_id,
    ) {}

    public function toArray(): array
    {
        return [
            'texts' => $this->texts,
            'conversation_id' => $this->conversation_id,
            'total_price' => $this->total_price,
            'message_id' => $this->message_id,
        ];
    }
}
