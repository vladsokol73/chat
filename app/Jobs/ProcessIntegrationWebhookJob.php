<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Services\Inbound\IncomingMessageService;
use App\Services\Integration\IntegrationRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessIntegrationWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [1, 5, 15, 60];

    private string $integrationId;

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(string $integrationId, array $payload)
    {
        $this->integrationId = $integrationId;
        $this->payload = $payload;
        $this->onQueue('integrations-inbound');
    }

    public function handle(IntegrationRouter $integrationRouter, IncomingMessageService $incomingMessageService): void
    {
        try {
            /** @var Integration $integration */
            $integration = Integration::query()->findOrFail($this->integrationId);

            // 1) Определяем транспорт
            $messaging = $integrationRouter->resolve($integration);

            // 2) Восстанавливаем Request (json)
            $content = json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $request = Request::create(
                uri: '/',
                method: 'POST',
                parameters: [],
                cookies: [],
                files: [],
                server: ['CONTENT_TYPE' => 'application/json'],
                content: $content ?: '{}',
            );

            // 3) Получаем DTO входящего сообщения
            $incomingDto = $messaging->handleWebhook($integration, $request);
            if (! $incomingDto) {
                return;
            }

            // 4) Доменная обработка
            $incomingMessageService->handle($integration, $incomingDto);
        } catch (Throwable $e) {
            Log::error('ProcessIntegrationWebhookJob.failed', [
                'integration_id' => $this->integrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
