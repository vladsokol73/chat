<?php

namespace App\Jobs;

use App\DTO\Funnel\FunnelMessageMetadataDto;
use App\DTO\Messaging\Outgoing\ChatActionDto;
use App\DTO\Queue\SendIntegrationMessagePayloadDto;
use App\Models\Chat;
use App\Models\Integration;
use App\Models\Media;
use App\Models\Message;
use App\Services\ClientService;
use App\Services\Dify\DifyMediaConverter;
use App\Services\DifyService;
use App\Services\FunnelService;
use App\Services\Integration\IntegrationRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessFunnelMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15, 60];

    public function __construct(
        private readonly string $integrationId,
        private readonly string $chatId,
    ) {
        $this->onQueue('integrations');
    }

    public function handle(
        DifyService $difyService,
        IntegrationRouter $integrationRouter,
        FunnelService $funnelService,
        ClientService $clientService,
        DifyMediaConverter $mediaConverter
    ): void {
        Log::info('funnel.batch.job_started', [
            'chat_id' => $this->chatId,
            'integration_id' => $this->integrationId,
            'current_time' => now()->toIso8601String(),
        ]);

        // Загружаем интеграцию и воронку
        $integration = Integration::query()->findOrFail($this->integrationId);

        $funnel = $funnelService->getByIntegrationId($this->integrationId);

        // Загружаем чат
        $chat = Chat::query()->findOrFail($this->chatId);

        // Читаем буфер сообщений (очистим его только после успешной обработки)
        $cacheKey = "funnel_buffer:{$chat->id}";
        $lockKey = "funnel_lock:{$chat->id}";

        $buffer = Cache::get($cacheKey, []);

        Log::info('funnel.batch.buffer_read', [
            'chat_id' => $chat->id,
            'cache_key' => $cacheKey,
            'buffer_size' => count($buffer),
            'buffer_exists' => ! empty($buffer),
            'attempt' => $this->attempts(),
        ]);

        if (empty($buffer)) {
            // Буфер пустой - возможно, это retry после успешной обработки
            Log::warning('funnel.buffer.empty', [
                'chat_id' => $chat->id,
                'cache_key' => $cacheKey,
                'attempt' => $this->attempts(),
            ]);

            // Очищаем lock на всякий случай
            Cache::forget($lockKey);

            // Если это не первая попытка и буфер пустой, значит уже была успешная обработка
            // Не ретраим job, чтобы избежать бесконечных ретраев
            if ($this->attempts() > 1) {
                Log::info('funnel.buffer.empty.skip_retry', [
                    'chat_id' => $chat->id,
                    'attempt' => $this->attempts(),
                ]);

                return;
            }

            // На первой попытке с пустым буфером - это странно, но не падаем
            return;
        }

        // Загружаем сообщения по ID из буфера вместе с медиа
        $messages = Message::query()
            ->whereIn('id', $buffer)
            ->with('media')
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            Log::warning('funnel.messages.not_found', [
                'chat_id' => $chat->id,
                'message_ids' => $buffer,
                'attempt' => $this->attempts(),
            ]);

            // Очищаем буфер и lock, если сообщения не найдены
            Cache::forget($cacheKey);
            Cache::forget($lockKey);

            return;
        }

        // Объединяем все тексты сообщений
        $combinedText = $messages
            ->pluck('text')
            ->filter()
            ->join("\n\n");

        // Собираем все медиа из сообщений
        /** @var \Illuminate\Support\Collection<int, Media> $mediaCollection */
        $mediaCollection = $messages
            ->flatMap(function (Message $message) {
                return $message->relationLoaded('media') ? $message->media : $message->media()->get();
            })
            ->filter();

        $difyFiles = $mediaCollection
            ->map(static fn (Media $media) => $mediaConverter->convert($media))
            ->filter()
            ->values()
            ->all();

        Log::info('funnel.batch.processing', [
            'chat_id' => $chat->id,
            'external_chat_id' => $chat->external_id,
            'client_id' => $chat->client_id,
            'messages_count' => $messages->count(),
            'combined_length' => strlen($combinedText),
            'media_count' => count($difyFiles),
            'buffer_details' => $messages->map(fn (Message $message) => [
                'message_id' => $message->id,
                'text_preview' => substr($message->text ?? '', 0, 30),
                'chat_id' => $message->chat_id,
                'has_media' => (($message->relationLoaded('media') ? $message->media : $message->media()->get())->isNotEmpty()),
            ])->toArray(),
        ]);

        /** @var string|null $typingKey */
        $typingKey = null;

        try {
            // Запускаем периодическую отправку typing во время обработки Dify
            $typingKey = "typing:{$chat->id}";
            Cache::put($typingKey, true, 300); // 5 минут на всякий случай

            // Первый typing отправляем сразу
            $service = $integrationRouter->resolve($integration);

            $service->sendChatAction($integration,
                new ChatActionDto(
                    service: $integration->service,
                    chatId: (string) $chat->external_id,
                )

            );

            // Запускаем job для периодической отправки typing каждые 4 секунды
            SendTypingJob::dispatch($integration->id, (string) $chat->external_id, $typingKey)
                ->delay(now()->addSeconds(4));

            // Отправляем запрос в Dify с объединённым текстом и медиа
            $response = $difyService->sendMessage(
                apiKey: $funnel->api_key,
                query: $combinedText,
                userId: (string) $chat->id,
                conversationId: $chat->conversation_id,
                files: $difyFiles
            );

            // Останавливаем отправку typing
            Cache::forget($typingKey);

            // Обновляем conversation_id у чата (если пришёл новый)
            if (! empty($response->conversation_id) && $chat->conversation_id !== $response->conversation_id) {
                $chat->update(['conversation_id' => $response->conversation_id]);
            }

            // Обновляем total_price у клиента (на уровне БД)
            if ($response->total_price > 0) {
                $clientService->addPrice($chat->client_id, $response->total_price);
            }

            // Отправляем все тексты из ответа с задержками (имитация печати)
            $texts = $response->texts ?? [];
            $pricePerMessage = count($texts) > 0 ? $response->total_price / count($texts) : 0;

            $cumulativeDelay = 0; // Накопительная задержка в секундах

            foreach ($texts as $index => $text) {
                if (empty($text)) {
                    continue; // Пропускаем пустые сообщения
                }

                // Первое сообщение отправляем моментально, остальные с задержкой
                if ($index === 0) {
                    $delay = 0;
                } else {
                    // Задержка = длина текста / 10 символов в секунду
                    $textLength = mb_strlen($text);
                    $delay = ceil($textLength / 10); // Округляем вверх
                    $cumulativeDelay += $delay;
                }

                // Диспатчим отправку сообщения с задержкой
                $payload = new SendIntegrationMessagePayloadDto(
                    chat_id: (string) $chat->external_id,
                    text: $text,
                    user_id: null,
                    show_typing: $index > 0,
                    funnel_metadata: new FunnelMessageMetadataDto(
                        dify_message_id: $response->message_id ?? null,
                        dify_conversation_id: $response->conversation_id ?? null,
                        price: $pricePerMessage,
                        sequence_index: $index,
                        sequence_total: count($texts),
                    )
                );

                $job = SendIntegrationMessageJob::dispatch(
                    integrationId: $integration->id,
                    payload: $payload
                );

                if ($cumulativeDelay > 0) {
                    $job->delay(now()->addSeconds($cumulativeDelay));
                }

                Log::info('funnel.message.dispatched', [
                    'chat_id' => $chat->id,
                    'sequence_index' => $index,
                    'text_length' => mb_strlen($text),
                    'delay_seconds' => $index === 0 ? 0 : $delay,
                    'cumulative_delay' => $cumulativeDelay,
                ]);
            }

            Log::info('funnel.batch.processed', [
                'chat_id' => $chat->id,
                'funnel_id' => $funnel->id,
                'input_messages_count' => $messages->count(),
                'output_messages_count' => count($texts),
                'price' => $response->total_price,
            ]);

            // Очищаем буфер и lock после успешной обработки
            Cache::forget($cacheKey);
            Cache::forget($lockKey);
        } catch (Throwable $e) {
            // Останавливаем typing в случае ошибки
            Cache::forget($typingKey);

            Log::error('funnel.processing.failed', [
                'chat_id' => $chat->id,
                'funnel_id' => $funnel->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // чтобы job ретраился
        }
    }
}
