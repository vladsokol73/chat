<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Доступ к приватному каналу чата — по умолчанию для любого аутентифицированного пользователя
Broadcast::channel('chat.{chatId}', function ($user, string $chatId) {
    // TODO: при необходимости добавить проверку доступа к конкретному чату
    return $user !== null;
});
