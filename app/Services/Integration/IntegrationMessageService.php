<?php

namespace App\Services\Integration;

use App\Http\Requests\Api\Integration\IntegrationWebhookRequest;
use App\Jobs\ProcessIntegrationWebhookJob;
use App\Models\Integration;
use RuntimeException;

readonly class IntegrationMessageService
{
    public function handleWebhook(IntegrationWebhookRequest $request): void
    {
        $integrationId = (string) $request->validated('integrationId');

        $integration = Integration::query()->find($integrationId);

        if (! $integration) {
            throw new RuntimeException('Integration not found');
        }

        // Асинхронная обработка входящих через очередь
        ProcessIntegrationWebhookJob::dispatch(
            integrationId: $integrationId,
            payload: $request->all()
        );
    }
}
