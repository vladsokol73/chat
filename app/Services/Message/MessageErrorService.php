<?php

namespace App\Services\Message;

use App\Models\Chat;
use App\Models\Integration;
use App\Models\Message;
use App\Services\Chat\ChatEventService;
use App\Services\Chat\ChatService;
use Throwable;

class MessageErrorService
{
    /**
     * @throws Throwable
     */
    public function handle(
        Chat $chat,
        Message $message,
        Integration $integration,
        Throwable $e,
        MessageService $messageService,
        ChatService $chatService,
        ChatEventService $eventService
    ): void {
        $error = $e->getMessage();
        $blocked = str_contains($error, '403') ||
            str_contains(strtolower($error), 'blocked');

        if ($blocked) {
            $messageService->fail($message, $error, '403');

            $chatService->blockChat($chat);

            $systemMessage = $messageService->createSystem(
                $chat,
                'Клиент заблокировал бота.',
                [
                    'reason' => 'blocked',
                    'error' => $error,
                ]
            );

            $eventService->dispatchMessageCreated($systemMessage);
            $eventService->dispatchChatUpdated($chat, $systemMessage);

            return;
        }

        $messageService->fail($message, $error, $e->getCode());
        throw $e;
    }
}
