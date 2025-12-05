<?php

namespace App\Jobs;

use App\DTO\Chat\ChatListDto;
use App\DTO\Chat\MessageSummaryDto;
use App\DTO\Client\ClientShortDto;
use App\Enums\MessageType;
use App\Events\ChatCreated;
use App\Services\Dev\MockGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GenerateChatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // достаточно короткий таймаут — job не должна долго висеть
    public int $timeout = 120;

    public int $tries = 1;

    private int $count;

    /**
     * Пауза между бродкастами в микросекундах (default 50ms).
     * Уменьшай/увеличивай по необходимости.
     */
    private int $sleepMicroseconds = 50_000;

    /**
     * @param  int  $count  сколько мок-чатов создать и отправить в сокет
     */
    public function __construct(int $count = 10, ?int $sleepMicroseconds = null)
    {
        $this->count = max(1, (int) $count);

        if (is_int($sleepMicroseconds)) {
            $this->sleepMicroseconds = max(0, $sleepMicroseconds);
        }
    }

    /**
     * Job генерирует моковые DTO и бродкастит их (не записывая ничего в БД).
     *
     * @param  MockGeneratorService  $mock  сервис для генерации случайных клиентов/сообщений
     */
    public function handle(MockGeneratorService $mock): void
    {
        Log::info('GenerateChatsJob started (mock broadcast only)', ['count' => $this->count]);

        for ($i = 0; $i < $this->count; $i++) {
            try {
                // Используем mock-сервис для получения "клиента" и "сообщения".
                $client = $mock->randomClient();
                $messageText = $mock->randomMessage();

                // Генерируем идентификаторы (UUID в строковом виде)
                $chatId = (string) Str::uuid();
                $messageId = (string) Str::uuid();
                $clientId = (string) ($client['id'] ?? Str::uuid());

                // Собираем DTO
                $messageDto = new MessageSummaryDto(
                    id: $messageId,
                    type: MessageType::TEXT,
                    text: (string) $messageText,
                    mediaGroupId: null,
                    media: null,
                    createdAt: now()->toIso8601String()
                );

                $clientShort = new ClientShortDto(
                    id: $clientId,
                    name: (string) ($client['name'] ?? 'Mock Client'),
                    avatar: $client['avatar'] ?? null,
                    tags: $client['tags'] ?? null,
                );

                $chatDto = new ChatListDto(
                    id: $chatId,
                    client: $clientShort,
                    lastMessage: $messageDto,
                    integrationName: 'test',
                    unreadCount: 0,
                    lastMessageAt: $messageDto->createdAt,
                    status: 'open'
                );

                // Бродкастим событие (Reverb / Pusher / Echo должен слушать это событие)
                event(new ChatCreated($chatDto));

                Log::info('Mock chat broadcasted', [
                    'iteration' => $i,
                    'chat_id' => $chatId,
                    'client_name' => $clientShort->name,
                ]);

                // Небольшая пауза, чтобы не зафлудить сокет
                if ($this->sleepMicroseconds > 0) {
                    usleep($this->sleepMicroseconds);
                }
            } catch (Throwable $e) {
                Log::error('GenerateChatsJob iteration failed', [
                    'iteration' => $i,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // продолжаем цикл, не прерываем job
            }
        }

        Log::info('GenerateChatsJob completed (mock broadcast only)', ['count' => $this->count]);
    }
}
