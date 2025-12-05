<?php

namespace App\Services\Message;

use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Jobs\SendIntegrationMessageJob;

class MessageQueueService
{
    public function dispatchOutgoing(string $integrationId, SendIntegrationMessagePayloadDto $payload): void
    {
        SendIntegrationMessageJob::dispatch($integrationId, $payload);
    }
}
