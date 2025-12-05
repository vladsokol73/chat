<?php

namespace App\Events;

use App\DTO\Chat\ChatListDto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatListDto $chat;

    public string $queue = 'broadcasts';

    public function __construct(ChatListDto $chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }

    public function broadcastAs(): string
    {
        return 'chat.created';
    }

    public function broadcastWith(): array
    {
        return $this->chat->toArray();
    }
}
