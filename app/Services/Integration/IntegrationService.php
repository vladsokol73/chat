<?php

namespace App\Services\Integration;

use App\DTO\Integration\IntegrationDto;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use Throwable;

class IntegrationService
{
    /**
     * Создание интеграции из DTO
     */
    public function create(IntegrationDto $dto): IntegrationDto
    {
        $integration = Integration::query()->create($dto->toArray());
        $integration->refresh();

        // Post-create hook: настройка вебхука для Telegram
        if (strtolower($integration->service->value) === 'telegram') {
            try {
                $this->setupTelegramWebhook($integration);
            } catch (Throwable $e) {
                // Удаляем созданную интеграцию в случае ошибки
                $integration->delete();

                Log::error('integration.webhook.setup_failed', [
                    'integration_id' => $integration->id,
                    'service' => $integration->service,
                    'error' => $e->getMessage(),
                ]);

                throw new RuntimeException('Failed to setup Telegram webhook: '.$e->getMessage(), 0, $e);
            }
            // Пытаемся подтянуть и сохранить аватар бота (не критично)
            $this->tryStoreTelegramAvatar($integration);
        }

        return IntegrationDto::fromModel($integration);
    }

    /**
     * Список всех интеграций
     */
    public function list(): array
    {
        return Integration::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Integration $integration) => IntegrationDto::fromModel($integration))
            ->all();
    }

    /**
     * Получение интеграции по ID
     */
    public function getById(string $id): IntegrationDto
    {
        $integration = Integration::query()->findOrFail($id);

        return IntegrationDto::fromModel($integration);
    }

    /**
     * Обновление интеграции
     */
    public function update(string $id, IntegrationDto $dto): IntegrationDto
    {
        $integration = Integration::query()->findOrFail($id);

        $oldService = $integration->service;
        $oldToken = $integration->token;

        $integration->update([
            'service' => $dto->service,
            'name' => $dto->name,
            'token' => $dto->token ?? $integration->token,
        ]);
        $integration->refresh();

        // Если изменился service или token для Telegram - обновляем webhook
        if (strtolower($integration->service->value) === 'telegram') {
            if ($oldService !== $integration->service || $oldToken !== $integration->token) {
                try {
                    $this->setupTelegramWebhook($integration);
                } catch (Throwable $e) {
                    // Откатываем изменения
                    $integration->update([
                        'service' => $oldService,
                        'token' => $oldToken,
                    ]);
                    $integration->refresh();

                    Log::error('integration.webhook.update_failed', [
                        'integration_id' => $integration->id,
                        'service' => $integration->service,
                        'error' => $e->getMessage(),
                    ]);

                    throw new RuntimeException('Failed to update Telegram webhook: '.$e->getMessage(), 0, $e);
                }
            }
        }

        return IntegrationDto::fromModel($integration);
    }

    /**
     * Удаление интеграции
     */
    public function delete(string $id): void
    {
        $integration = Integration::query()->findOrFail($id);

        // Удаляем webhook для Telegram перед удалением интеграции
        if (strtolower($integration->service->value) === 'telegram' && ! empty($integration->token)) {
            $this->deleteTelegramWebhook($integration);
        }

        $integration->delete();
    }

    /**
     * Настройка webhook для Telegram
     */
    private function setupTelegramWebhook(Integration $integration): void
    {
        $token = $integration->token;

        if (empty($token)) {
            throw new RuntimeException('Telegram bot token is empty');
        }

        $baseUrl = config('app.url_with_prefix') ?: config('app.url');
        $webhookUrl = rtrim((string) $baseUrl, '/').'/webhook/integration/'.urlencode((string) $integration->id);

        $response = Http::timeout(10)
            ->asForm()
            ->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl,
            ]);

        if (! $response->successful()) {
            $error = $response->json('description', 'Unknown error');
            throw new RuntimeException("Telegram API error: {$error}");
        }

        $result = $response->json('result');
        if (! $result) {
            $description = $response->json('description', 'Webhook setup returned false');
            throw new RuntimeException("Telegram webhook setup failed: {$description}");
        }

        Log::info('integration.webhook.setup_success', [
            'integration_id' => $integration->id,
            'webhook_url' => $webhookUrl,
        ]);
    }

    /**
     * Удаление webhook для Telegram
     */
    private function deleteTelegramWebhook(Integration $integration): void
    {
        $token = $integration->token;

        if (empty($token)) {
            return;
        }

        Http::asForm()->post("https://api.telegram.org/bot{$token}/deleteWebhook");
    }

    /**
     * Попытаться сохранить аватар Telegram-бота интеграции в S3 (avatars/integrations/{id}/avatar.ext)
     */
    private function tryStoreTelegramAvatar(Integration $integration): void
    {
        try {
            $token = $integration->token;
            if (empty($token)) {
                return;
            }

            $bot = new Nutgram($token);
            $me = $bot->getMe();
            if ($me === null || empty($me->id)) {
                return;
            }

            $photos = $bot->getUserProfilePhotos(user_id: $me->id, limit: 1);
            if (empty($photos->photos) || empty($photos->photos[0])) {
                return; // у бота нет аватара
            }
            $sizes = $photos->photos[0];
            $largest = $sizes[count($sizes) - 1];
            $file = $bot->getFile($largest->file_id);
            if ($file === null || empty($file->file_path)) {
                return;
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'tg_');
            $bot->downloadFile($file, $tmpPath);

            $ext = pathinfo($file->file_path, PATHINFO_EXTENSION) ?: 'jpg';
            $storagePath = "avatars/integrations/{$integration->id}/avatar.{$ext}";
            Storage::disk('s3')->put($storagePath, file_get_contents($tmpPath), ['visibility' => 'public']);
            @unlink($tmpPath);

            $publicUrl = Storage::disk('s3')->url($storagePath);
            $integration->updateQuietly(['avatar_url' => $publicUrl]);
        } catch (Throwable $e) {
            Log::warning('integration.telegram.avatar_fetch_failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
