<?php

namespace App\Services;

use App\DTO\Funnel\FunnelDto;
use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Jobs\ProcessFunnelMessageJob;
use App\Models\Chat;
use App\Models\Funnel;
use App\Models\Integration;
use App\Models\Message;
use App\Services\Message\MessageQueueService;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FunnelService
{
    public function __construct(
        private readonly MessageQueueService $messageQueueService
    ) {}

    /**
     * Получить список всех воронок
     *
     * @return array<FunnelDto>
     */
    public function list(): array
    {
        /** @var Collection<Funnel> $funnels */
        $funnels = Funnel::query()->with('integration')->get();

        return $funnels->map(fn ($funnel) => FunnelDto::fromModel($funnel))->all();
    }

    /**
     * Создать новую воронку
     */
    public function create(FunnelDto $dto): FunnelDto
    {
        $funnel = Funnel::query()->create($dto->toArray());
        $funnel->refresh();
        $funnel->load('integration');

        return FunnelDto::fromModel($funnel);
    }

    /**
     * Обновить воронку
     */
    public function update(string $id, FunnelDto $dto): FunnelDto
    {
        $funnel = Funnel::query()->findOrFail($id);

        $funnel->update([
            'name' => $dto->name,
            'integration_id' => $dto->integration_id,
            'api_key' => $dto->api_key ?? $funnel->api_key,
        ]);
        $funnel->refresh();
        $funnel->load('integration');

        return FunnelDto::fromModel($funnel);
    }

    /**
     * Удалить воронку
     */
    public function delete(string $id): bool
    {
        $funnel = Funnel::query()->findOrFail($id);

        return $funnel->delete();
    }

    /**
     * @return void
     *              Управление буффером + запуск ProcessFunnelMessageJob
     */
    public function bufferAndDispatch(Integration $integration, Chat $chat, Message $message): void
    {
        $funnel = Funnel::query()
            ->where('integration_id', $integration->id)
            ->first();

        if (! $funnel) {
            return;
        }

        $cacheKey = "funnel_buffer:{$chat->id}";
        $lockKey = "funnel_lock:{$chat->id}";

        // Достаём буфер
        $buffer = Cache::get($cacheKey, []);

        // Добавляем ID сообщения
        $buffer[] = $message->id;

        // Обновляем буфер
        Cache::put($cacheKey, $buffer, now()->addSeconds(40));

        // Лочим запуск джобы
        $lockAcquired = Cache::add($lockKey, true, now()->addSeconds(15));

        if ($lockAcquired) {
            ProcessFunnelMessageJob::dispatch(
                integrationId: $integration->id,
                chatId: (string) $chat->id
            )->delay(now()->addSeconds(10));
        }
    }

    public function getByIntegrationId(string $integrationId): ?Funnel
    {
        return Funnel::query()
            ->where('integration_id', $integrationId)
            ->firstOrFail();
    }

    /**
     * Проверить, есть ли воронка для интеграции
     */
    public function hasFunnel(Integration $integration): bool
    {
        return Funnel::query()
            ->where('integration_id', $integration->id)
            ->exists();
    }

    /**
     * Отправить сообщение в существующий чат по внешнему chat_id (для туннеля).
     */
    public function sendMessageFromTunnel(string $chatId, string $text): void
    {
        $chat = Chat::query()->findOrFail($chatId);

        $payload = new SendIntegrationMessagePayloadDto(
            chat_id: $chat->external_id,
            text: $text,
            user_id: null,
            show_typing: false,
            message_id: Str::uuid()->toString()
        );

        $this->messageQueueService->dispatchOutgoing($chat->integration_id, $payload);
    }
}
