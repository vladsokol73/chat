<?php

namespace App\Providers;

use App\Contracts\Media\MediaStorage;
use App\Contracts\Messaging\MediaExtractorInterface;
use App\Contracts\Messaging\MessagingServiceInterface;
use App\Contracts\Outgoing\OutgoingMessageServiceInterface;
use App\Services\Media\S3MediaStorageService;
use App\Services\Messaging\TelegramMessagingService;
use App\Services\Outgoing\MockOutgoingMessageService;
use App\Services\Outgoing\RealOutgoingMessageService;
use App\Support\Messaging\Extractors\TelegramMediaExtractor;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(MediaExtractorInterface::class, TelegramMediaExtractor::class);
        $this->app->bind(MessagingServiceInterface::class, TelegramMessagingService::class);

        $this->app->bind(MediaStorage::class, S3MediaStorageService::class);

        $this->bindImplementationMap([
            OutgoingMessageServiceInterface::class => [
                'real' => RealOutgoingMessageService::class,
                'mock' => MockOutgoingMessageService::class,
            ],
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Форсируем корневой URL с префиксом /api
        $urlWithPrefix = config('app.url_with_prefix');
        if (! empty($urlWithPrefix)) {
            URL::forceRootUrl(rtrim($urlWithPrefix, '/'));
        }

        // Форсируем HTTPS, если включено
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Универсальный метод для биндинга интерфейсов на разные реализации.
     *
     * @param  array<class-string, array{real: class-string, mock: class-string}>  $map
     */
    private function bindImplementationMap(array $map): void
    {
        foreach ($map as $interface => $implementations) {

            $implementation = $this->chooseImplementation($implementations);

            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Выбирает реализацию (реальную или mock) в зависимости от окружения.
     *
     * @param  array{real: class-string, mock: class-string}  $implementations
     */
    private function chooseImplementation(array $implementations): string
    {
        // Для dev/test будет мок
        if (app()->environment('local', 'testing')) {
            return $implementations['mock'];
        }

        // Для продакшена — реальный сервис
        return $implementations['real'];
    }
}
