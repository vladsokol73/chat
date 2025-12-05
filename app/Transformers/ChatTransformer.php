<?php

namespace App\Transformers;

use App\DTO\Chat\ChatDto;
use App\DTO\Chat\ChatListDto;
use App\DTO\Chat\MessageDto;

class ChatTransformer
{
    public function transformChatListItem(ChatListDto $chat): array
    {
        return $chat->toArray();
    }

    /**
     * Возвращает только массив data (без meta)
     *
     * @param  ChatListDto[]  $chats
     * @return array<int, array>
     */
    public function transformListData(array $chats): array
    {
        return array_map([$this, 'transformChatListItem'], $chats);
    }

    public function transformChatDetail(ChatDto $chat): array
    {
        return $chat->toArray();
    }

    public function transformMessage(MessageDto $message): array
    {
        return $message->toArray();
    }

    // Если нужно собрать meta в одном месте (опционально)
    public function buildMeta(array $meta): array
    {
        // Можно дополнить/нормализовать поля meta здесь
        return $meta;
    }
}
