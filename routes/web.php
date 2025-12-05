<?php

use App\Http\Controllers\Api\AuthSessionController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\FunnelController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\IntegrationMessageController;
use App\Http\Controllers\Api\SsoExchangeController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslatorController;
use App\Http\Controllers\Dev\LoginAsOneController;
use App\Http\Controllers\Dev\MockAttackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dev-only: авторизация одного пользователя из БД для локальной разработки
if (app()->isLocal()) {
    Route::get('/dev/login-as-one', LoginAsOneController::class);
}

// Обмен JWT -> session (только с ERP-токеном). Без 'api' — чтобы работали cookie-сессии
Route::middleware(['erp.jwt'])->group(function () {
    Route::post('/auth/sso-exchange', SsoExchangeController::class);
});

// Публичный профиль (определяет авторизован ли пользователь)
Route::get('/me', [AuthSessionController::class, 'me']);

// Сессионные API-ручки (после логина)
// Публичная ручка для туннеля (по api-key в заголовке авторизации)
Route::post('/funnels/send-message', [FunnelController::class, 'sendMessage']);

Route::middleware(['auth.api'])->group(function () {
    Route::get('/chats', [ChatController::class, 'list']);
    Route::get('/chats/{chatId}', [ChatController::class, 'detail']);
    Route::post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chats/{chatId}/read', [ChatController::class, 'markMessagesAsRead']);
    Route::put('/chats/{chatId}/status', [ChatController::class, 'updateStatus']);

    Route::post('/chats/media', [ChatController::class, 'uploadMedia']);

    // Логаут
    Route::post('/auth/logout', [AuthSessionController::class, 'logout']);

    // Теги
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::get('/tags/{id}', [TagController::class, 'show']);
    Route::put('/tags/{id}', [TagController::class, 'update']);
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);

    // Интеграции (CRUD)
    Route::get('/integrations', [IntegrationController::class, 'index']);
    Route::post('/integrations', [IntegrationController::class, 'store']);
    Route::get('/integrations/{id}', [IntegrationController::class, 'show']);
    Route::put('/integrations/{id}', [IntegrationController::class, 'update']);
    Route::delete('/integrations/{id}', [IntegrationController::class, 'destroy']);

    // Воронки
    Route::get('/funnels', [FunnelController::class, 'index']);
    Route::post('/funnels', [FunnelController::class, 'store']);
    Route::put('/funnels/{id}', [FunnelController::class, 'update']);
    Route::delete('/funnels/{id}', [FunnelController::class, 'destroy']);

    // Кастомные поля
    Route::get('/custom-fields', [CustomFieldController::class, 'index']);
    Route::post('/custom-fields', [CustomFieldController::class, 'store']);
    Route::get('/custom-fields/{id}', [CustomFieldController::class, 'show']);
    Route::put('/custom-fields/{id}', [CustomFieldController::class, 'update']);
    Route::delete('/custom-fields/{id}', [CustomFieldController::class, 'destroy']);
    Route::prefix('mock/attack')->group(function () {
        // Генерация моковых чатов
        Route::post('/chats', [MockAttackController::class, 'generateChats']);
        // Генерация моковых сообщений (опционально по chatId)
        Route::post('/chat/{chatId?}', [MockAttackController::class, 'generateMessages']);
    });

    // Переводчик
    Route::get('/translate', [TranslatorController::class, 'translate']);

    // Клиенты
    Route::put('/clients/{clientId}', [ClientController::class, 'update']);
});

Route::post('/webhook/integration/{integrationId}', [IntegrationMessageController::class, 'integrationWebhook'])
    ->name('integration.webhook');
