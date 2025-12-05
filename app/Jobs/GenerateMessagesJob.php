<?php

namespace App\Jobs;

use App\DTO\Chat\MessageDto;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\ChatMessageCreated;
use App\Services\Dev\MockGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GenerateMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    private ?string $chatId;

    private int $messagesCount;

    /**
     * Пауза между сообщениями (в микросекундах)
     */
    private int $sleepMicroseconds = 2_000_000; // 2 секунды

    /**
     * @param  array|null  $options  ['chat_id' => string|null, 'count' => int]
     */
    public function __construct(?array $options = null)
    {
        $this->chatId = $options['chatId'] ?? null;
        $this->messagesCount = max(1, (int) ($options['count'] ?? 20));
    }

    /**
     * Генерирует моковые сообщения и отправляет их по WebSocket без записи в БД.
     */
    public function handle(MockGeneratorService $mock): void
    {
        Log::info('GenerateMessagesJob started (mock broadcast only)', [
            'chat_id' => $this->chatId,
            'count' => $this->messagesCount,
        ]);

        // если chat_id не указан — создаём 5 временных UUID чатов
        $targetChats = $this->chatId
            ? [$this->chatId]
            : array_map(static fn () => (string) Str::uuid(), range(1, 5));

        foreach ($targetChats as $chatId) {
            for ($i = 0; $i < $this->messagesCount; $i++) {
                try {
                    $direction = $i % 2 === 0 ? MessageDirection::OUT : MessageDirection::IN;
                    $userId = (string) Str::uuid();
                    $text = $mock->randomMessage();

                    $messageDto = new MessageDto(
                        id: (string) Str::uuid(),
                        chatId: $chatId,
                        userId: $userId,
                        direction: $direction,
                        type: MessageType::TEXT,
                        status: MessageStatus::SENT,
                        text: $text,
                        mediaGroupId: null,
                        media: null,
                        createdAt: now()->toIso8601String(),
                    );

                    // Отправляем событие в приватный канал chat.{chatId}
                    event(new ChatMessageCreated($messageDto));

                    Log::info('Mock message broadcasted', [
                        'chat_id' => $chatId,
                        'direction' => $direction,
                        'text' => $text,
                    ]);

                    if ($this->sleepMicroseconds > 0) {
                        usleep($this->sleepMicroseconds);
                    }
                } catch (Throwable $e) {
                    Log::error('GenerateMessagesJob iteration failed', [
                        'chat_id' => $chatId,
                        'iteration' => $i,
                        'error' => $e->getMessage(),
                    ]);
                    // продолжаем, не падаем полностью
                }
            }
        }

        Log::info('GenerateMessagesJob completed (mock broadcast only)', [
            'total_messages' => $this->messagesCount * count($targetChats),
        ]);
    }
}
