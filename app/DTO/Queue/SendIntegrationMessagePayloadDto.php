<?php

namespace App\DTO\Queue;

use App\Contracts\DTO\FromArrayInterface;
use App\Contracts\DTO\ToArrayInterface;
use App\DTO\Funnel\FunnelMessageMetadataDto;

final readonly class SendIntegrationMessagePayloadDto implements FromArrayInterface, ToArrayInterface
{
    /**
     * @param  string[]|null  $media_ids
     */
    public function __construct(
        public string $chat_id,
        public string $text,
        public ?string $user_id,
        public bool $show_typing,
        public ?string $message_id = null,
        public ?FunnelMessageMetadataDto $funnel_metadata = null,
        public ?array $media_ids = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            chat_id: (string) $data['chat_id'],
            text: (string) $data['text'],
            user_id: $data['user_id'] ?? null,
            show_typing: (bool) ($data['show_typing'] ?? false),
            message_id: $data['message_id'] ?? null,
            funnel_metadata: isset($data['funnel_metadata'])
                ? FunnelMessageMetadataDto::fromArray($data['funnel_metadata'])
                : null,
            media_ids: isset($data['media_ids']) && is_array($data['media_ids'])
                ? array_map('strval', $data['media_ids'])
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'chat_id' => $this->chat_id,
            'text' => $this->text,
            'user_id' => $this->user_id,
            'show_typing' => $this->show_typing,
            'message_id' => $this->message_id,
            'funnel_metadata' => $this->funnel_metadata?->toArray(),
            'media_ids' => $this->media_ids,
        ];
    }
}
