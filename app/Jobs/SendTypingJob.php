<?php

namespace App\Jobs;

use App\DTO\Messaging\Outgoing\ChatActionDto;
use App\Models\Integration;
use App\Services\Integration\IntegrationRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTypingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        private readonly string $integrationId,
        private readonly string $externalChatId,
        private readonly string $typingKey
    ) {
        $this->onQueue('integrations');
    }

    public function handle(IntegrationRouter $integrationRouter): void
    {
        // Проверяем, нужно ли ещё показывать typing
        if (! Cache::has($this->typingKey)) {
            Log::debug('typing.stopped', [
                'typing_key' => $this->typingKey,
                'external_chat_id' => $this->externalChatId,
            ]);

            return;
        }

        $integration = Integration::query()->find($this->integrationId);

        if (! $integration) {
            return;
        }

        try {
            $service = $integrationRouter->resolve($integration);
            $service->sendChatAction($integration, new ChatActionDto(
                service: $integration->service,
                chatId: $this->externalChatId
            ));

            Log::debug('typing.sent', [
                'typing_key' => $this->typingKey,
                'external_chat_id' => $this->externalChatId,
            ]);

            // Планируем следующую отправку typing через 4 секунды (т.к. typing живёт 5 сек)
            self::dispatch($this->integrationId, $this->externalChatId, $this->typingKey)
                ->delay(now()->addSeconds(4));
        } catch (Throwable $e) {
            Log::warning('typing.failed', [
                'typing_key' => $this->typingKey,
                'external_chat_id' => $this->externalChatId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
