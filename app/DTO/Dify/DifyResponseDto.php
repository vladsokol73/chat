<?php

namespace App\DTO\Dify;

use App\Contracts\DTO\FromArrayInterface;

final readonly class DifyResponseDto implements FromArrayInterface
{
    public function __construct(
        public ?string $answer,
        public ?array $messages,
        public ?string $conversation_id,
        public ?string $message_id,
        public float $total_price,
        public array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            answer: $data['answer'] ?? null,
            messages: $data['messages'] ?? null,
            conversation_id: $data['conversation_id'] ?? null,
            message_id: $data['message_id'] ?? null,
            total_price: (float) ($data['metadata']['usage']['total_price'] ?? 0.0),
            raw: $data,
        );
    }

    /**
     * Универсальный парсер текста.
     *
     * @return array<int, string>
     */
    public function parsedTexts(): array
    {
        // 1. Формат: простой текст в answer
        if ($this->answer && ! str_starts_with(trim($this->answer), '{')) {
            return [$this->answer];
        }

        // 2. Формат: answer содержит JSON
        if ($this->answer && str_starts_with(trim($this->answer), '{')) {
            $decoded = json_decode($this->answer, true);

            if (! empty($decoded['messages']) && is_array($decoded['messages'])) {
                return array_filter(array_map(
                    fn ($m) => trim((string) ($m['text'] ?? '')),
                    $decoded['messages']
                ));
            }
        }

        // 3. Формат: messages[] на верхнем уровне
        if ($this->messages) {
            return array_filter(array_map(
                fn ($m) => trim((string) ($m['text'] ?? '')),
                $this->messages
            ));
        }

        return [];
    }
}
