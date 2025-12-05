<?php

namespace App\Services\Channels;

use Carbon\Carbon;

class TelegramUpdateNormalizer
{
    /**
     * Нормализует апдейт Telegram в плоскую структуру нашего сообщения.
     * Возвращает null, если апдейт не поддерживается.
     * Возвращаемые поля (минимум):
     * - chat_id (int)
     * - author_id (int|null)
     * - text (string)
     * - type (string): text|photo|video|animation|document|audio|voice|video_note|sticker
     * - media (array|null): метаданные вложения (file_id и др.)
     * - media_group_id (string|null)
     * - external_message_id (string)
     * - reply_to_external_message_id (string|null)
     * - created_at (ISO8601 string)
     */
    public function normalize(array $update): ?array
    {
        $createdAt = Carbon::now()->toIso8601String();

        // 1) callback_query → как входящее служебное сообщение
        if (isset($update['callback_query'])) {
            $cb = $update['callback_query'];
            $msg = $cb['message'] ?? null;
            $chatId = $msg['chat']['id'] ?? null;
            $from = $cb['from']['id'] ?? null;
            $data = (string) ($cb['data'] ?? '');
            if ($chatId !== null && $from !== null && $data !== '') {
                return [
                    'chat_id' => (int) $chatId,
                    'author_id' => (int) $from,
                    'text' => '[callback] '.$data,
                    'type' => 'text',
                    'media' => null,
                    'media_group_id' => null,
                    'external_message_id' => (string) ($msg['message_id'] ?? ''),
                    'reply_to_external_message_id' => $msg['reply_to_message']['message_id'] ?? null,
                    'created_at' => $createdAt,
                ];
            }
        }

        // 2) message/edited_message/channel_post/edited_channel_post
        $msg = $update['message']
            ?? $update['edited_message']
            ?? $update['channel_post']
            ?? $update['edited_channel_post']
            ?? null;
        if (! $msg) {
            return null;
        }

        $chatId = $msg['chat']['id'] ?? null;
        $from = $msg['from']['id'] ?? ($msg['sender_chat']['id'] ?? null);
        if ($chatId === null || $from === null) {
            return null;
        }

        // базовый текст: text или caption
        $text = (string) ($msg['text'] ?? $msg['caption'] ?? '');

        // определить тип и собрать метаданные вложения
        $type = 'text';
        $media = null;

        if (isset($msg['photo']) && is_array($msg['photo'])) {
            $type = 'photo';
            $sizes = $msg['photo'];
            $chosen = end($sizes) ?: ($sizes[0] ?? null); // наибольшее разрешение
            $media = [
                'kind' => 'photo',
                'file_id' => $chosen['file_id'] ?? null,
                'file_unique_id' => $chosen['file_unique_id'] ?? null,
                'width' => $chosen['width'] ?? null,
                'height' => $chosen['height'] ?? null,
                'sizes' => $sizes,
            ];
            $text = trim('[photo] '.$text);
        } elseif (isset($msg['video'])) {
            $type = 'video';
            $v = $msg['video'];
            $media = [
                'kind' => 'video',
                'file_id' => $v['file_id'] ?? null,
                'file_unique_id' => $v['file_unique_id'] ?? null,
                'width' => $v['width'] ?? null,
                'height' => $v['height'] ?? null,
                'duration' => $v['duration'] ?? null,
                'mime' => $v['mime_type'] ?? null,
                'thumbnail' => $v['thumbnail'] ?? ($v['thumb'] ?? null),
            ];
            $text = trim('[video] '.$text);
        } elseif (isset($msg['animation'])) {
            $type = 'animation';
            $a = $msg['animation'];
            $media = [
                'kind' => 'animation',
                'file_id' => $a['file_id'] ?? null,
                'file_unique_id' => $a['file_unique_id'] ?? null,
                'width' => $a['width'] ?? null,
                'height' => $a['height'] ?? null,
                'duration' => $a['duration'] ?? null,
                'mime' => $a['mime_type'] ?? null,
                'thumbnail' => $a['thumbnail'] ?? ($a['thumb'] ?? null),
            ];
            $text = trim('[animation] '.$text);
        } elseif (isset($msg['document'])) {
            $type = 'document';
            $d = $msg['document'];
            $media = [
                'kind' => 'document',
                'file_id' => $d['file_id'] ?? null,
                'file_unique_id' => $d['file_unique_id'] ?? null,
                'file_name' => $d['file_name'] ?? null,
                'mime' => $d['mime_type'] ?? null,
                'thumbnail' => $d['thumbnail'] ?? ($d['thumb'] ?? null),
            ];
            $text = trim('[document] '.$text);
        } elseif (isset($msg['audio'])) {
            $type = 'audio';
            $a = $msg['audio'];
            $media = [
                'kind' => 'audio',
                'file_id' => $a['file_id'] ?? null,
                'file_unique_id' => $a['file_unique_id'] ?? null,
                'duration' => $a['duration'] ?? null,
                'mime' => $a['mime_type'] ?? null,
                'performer' => $a['performer'] ?? null,
                'title' => $a['title'] ?? null,
            ];
            $text = trim('[audio] '.$text);
        } elseif (isset($msg['voice'])) {
            $type = 'voice';
            $v = $msg['voice'];
            $media = [
                'kind' => 'voice',
                'file_id' => $v['file_id'] ?? null,
                'file_unique_id' => $v['file_unique_id'] ?? null,
                'duration' => $v['duration'] ?? null,
                'mime' => $v['mime_type'] ?? null,
            ];
            $text = trim('[voice] '.$text);
        } elseif (isset($msg['video_note'])) {
            $type = 'video_note';
            $vn = $msg['video_note'];
            $media = [
                'kind' => 'video_note',
                'file_id' => $vn['file_id'] ?? null,
                'file_unique_id' => $vn['file_unique_id'] ?? null,
                'duration' => $vn['duration'] ?? null,
                'length' => $vn['length'] ?? null,
                'thumbnail' => $vn['thumbnail'] ?? ($vn['thumb'] ?? null),
            ];
            $text = trim('[video_note] '.$text);
        } elseif (isset($msg['sticker'])) {
            $type = 'sticker';
            $s = $msg['sticker'];
            $media = [
                'kind' => 'sticker',
                'file_id' => $s['file_id'] ?? null,
                'file_unique_id' => $s['file_unique_id'] ?? null,
                'emoji' => $s['emoji'] ?? null,
                'is_animated' => $s['is_animated'] ?? null,
                'is_video' => $s['is_video'] ?? null,
                'thumbnail' => $s['thumbnail'] ?? ($s['thumb'] ?? null),
            ];
            $emoji = $s['emoji'] ?? '';
            $text = trim('[sticker] '.$emoji.' '.$text);
        } elseif (isset($msg['location'])) {
            $type = 'text';
            $lat = $msg['location']['latitude'] ?? '';
            $lon = $msg['location']['longitude'] ?? '';
            $text = trim("[location] {$lat},{$lon} ".$text);
        } elseif (isset($msg['contact'])) {
            $type = 'text';
            $phone = $msg['contact']['phone_number'] ?? '';
            $name = trim(($msg['contact']['first_name'] ?? '').' '.($msg['contact']['last_name'] ?? ''));
            $text = trim("[contact] {$name} {$phone} ".$text);
        }

        if ($text === '') {
            // Если совсем пусто (редкий случай), задаём плейсхолдер по типу
            $text = $type !== 'text' ? '['.$type.']' : '';
        }

        return [
            'chat_id' => (int) $chatId,
            'author_id' => (int) $from,
            'text' => $text,
            'type' => $type,
            'media' => $media,
            'media_group_id' => $msg['media_group_id'] ?? null,
            'external_message_id' => (string) ($msg['message_id'] ?? ''),
            'reply_to_external_message_id' => $msg['reply_to_message']['message_id'] ?? null,
            'created_at' => $createdAt,
        ];
    }
}
