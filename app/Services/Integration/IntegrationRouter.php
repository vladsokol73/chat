<?php

namespace App\Services\Integration;

use App\Contracts\Messaging\MessagingServiceInterface;
use App\Enums\ServiceType;
use App\Models\Integration;
use App\Services\Messaging\TelegramMessagingService;
use InvalidArgumentException;

class IntegrationRouter
{
    public function resolve(Integration $integration): MessagingServiceInterface
    {
        $service = $integration->service->value;

        return match ($service) {
            ServiceType::TELEGRAM->value => app(TelegramMessagingService::class),

            default => throw new InvalidArgumentException(
                sprintf('Unsupported integration for service "%s".', $service)
            ),
        };
    }
}
