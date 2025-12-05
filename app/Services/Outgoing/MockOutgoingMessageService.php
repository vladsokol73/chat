<?php

namespace App\Services\Outgoing;

use App\Contracts\Outgoing\OutgoingMessageServiceInterface;
use App\Models\Integration;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MockOutgoingMessageService implements OutgoingMessageServiceInterface
{
    public function send(Message $message, Integration $integration, array $payload): array
    {
        Log::info('[MOCK] Sending message (dev mode)', [
            'chat_id' => $payload['chat_id'],
            'text' => $payload['text'],
        ]);

        return [
            'message_id' => Str::uuid()->toString(),
        ];
    }

    public function sendTyping(Integration $integration, string $chatId): void
    {
        Log::info('[MOCK] sending typing...', [
            'chat_id' => $chatId,
            'integration_id' => $integration->id,
        ]);
    }
}
