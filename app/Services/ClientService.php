<?php

namespace App\Services;

use App\DTO\Client\UpdateClientDto;
use App\DTO\Messaging\Incoming\IncomingMessageDto;
use App\Models\Client;
use App\Models\Integration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use Throwable;

class ClientService
{
    public function findOrCreateFromIncoming(Integration $integration, IncomingMessageDto $dto): Client
    {
        $raw = $dto->raw;

        $from = $raw['message']['from'] ?? [];
        $chatObj = $raw['message']['chat'] ?? [];

        $first = $from['first_name'] ?? $chatObj['first_name'] ?? null;
        $last = $from['last_name'] ?? $chatObj['last_name'] ?? null;
        $username = $from['username'] ?? $chatObj['username'] ?? null;

        $displayName = trim(implode(' ', array_filter([$first, $last])));
        if ($displayName === '') {
            $displayName = $username ? (string) $username : ('Telegram User '.substr((string) $dto->externalChatId, -4));
        }
        $phone = $username ? ('@'.$username) : '';

        $client = Client::query()->firstOrCreate(
            [
                'integration_id' => $integration->id,
                'external_id' => $dto->externalChatId,
            ],
            [
                'name' => $displayName,
                'phone' => $phone,
                'avatar' => '',
            ]
        );

        // При первом создании клиента для Telegram пробуем подтянуть аватар и сохранить в S3
        if (strtolower($integration->service->value) === 'telegram'
            && $client->wasRecentlyCreated
            && ((string) ($client->avatar ?? '')) === ''
        ) {
            $this->tryStoreTelegramClientAvatar($integration, $client, $raw);
        }

        $updates = [];
        if (isset($client->name) && str_starts_with($client->name, 'Telegram User ') && $displayName !== $client->name) {
            $updates['name'] = $displayName;
        }
        if ((string) ($client->phone ?? '') === '' && $phone !== '') {
            $updates['phone'] = $phone;
        }
        if ($updates) {
            $client->update($updates);
        }

        return $client;
    }

    public function addPrice(string $clientId, float $amount): void
    {
        Client::query()
            ->where('id', $clientId)
            ->increment('total_price', $amount, [
                'updated_at' => now(),
            ]);
    }

    /**
     * Update client data
     */
    public function update(Client $client, UpdateClientDto $dto): void
    {
        $updates = [];

        if ($dto->name !== null) {
            $updates['name'] = $dto->name;
        }

        if ($dto->phone !== null) {
            $updates['phone'] = $dto->phone;
        }

        if ($dto->comment !== null) {
            $updates['comment'] = $dto->comment;
        }

        // Handle avatar upload
        if ($dto->avatar !== null) {
            // Delete old avatar if exists
            $oldAvatar = $client->avatar;
            if (! empty($oldAvatar) && is_string($oldAvatar)) {
                try {
                    // oldAvatar может быть как ключом S3, так и публичным URL
                    $key = $oldAvatar;
                    if (str_starts_with($oldAvatar, 'http://') || str_starts_with($oldAvatar, 'https://')) {
                        $path = parse_url($oldAvatar, PHP_URL_PATH) ?: '';
                        $key = ltrim((string) $path, '/');
                    }
                    if ($key !== '') {
                        Storage::disk('s3')->delete($key);
                    }
                } catch (\Exception $e) {
                    // Ignore deletion errors
                }
            }

            // Upload new avatar
            $extension = $dto->avatar->getClientOriginalExtension() ?: 'jpg';
            $storagePath = "avatars/clients/{$client->id}/avatar.{$extension}";

            Storage::disk('s3')->put(
                $storagePath,
                file_get_contents($dto->avatar->getRealPath()),
                [
                    'visibility' => 'public',
                    'ContentType' => $dto->avatar->getMimeType(),
                ]
            );

            $updates['avatar'] = Storage::disk('s3')->url($storagePath);
        }

        if (! empty($updates)) {
            $client->updateQuietly($updates);
        }
    }

    /**
     * Попытаться сохранить аватар Telegram-пользователя в S3 (avatars/clients/{clientId}/avatar.ext)
     */
    private function tryStoreTelegramClientAvatar(Integration $integration, Client $client, array $raw): void
    {
        try {
            $token = $integration->token;
            if (empty($token)) {
                return;
            }

            $from = $raw['message']['from'] ?? [];
            $chatObj = $raw['message']['chat'] ?? [];
            $userId = $from['id'] ?? $chatObj['id'] ?? null;
            if (empty($userId)) {
                return;
            }

            $bot = new Nutgram($token);
            $photos = $bot->getUserProfilePhotos(user_id: (int) $userId, limit: 1);
            if (empty($photos->photos) || empty($photos->photos[0])) {
                return; // у пользователя нет аватара
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
            $storagePath = "avatars/clients/{$client->id}/avatar.{$ext}";
            Storage::disk('s3')->put($storagePath, file_get_contents($tmpPath), ['visibility' => 'public']);
            @unlink($tmpPath);

            $publicUrl = Storage::disk('s3')->url($storagePath);
            $client->updateQuietly(['avatar' => $publicUrl]);
        } catch (Throwable $e) {
            Log::warning('client.telegram.avatar_fetch_failed', [
                'integration_id' => $integration->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
