<?php

namespace App\Models;

use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $chat_id
 * @property string|null $user_id
 * @property string|null $external_message_id
 * @property MessageDirection $direction
 * @property MessageType $type
 * @property MessageStatus $status
 * @property string|null $text
 * @property array<string, mixed>|null $payload
 * @property string|null $reply_to_message_id
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $read_at
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Chat $chat
 * @property-read User|null $user
 * @property-read Message|null $replyTo
 * @property-read Collection<int, Media> $media
 * @property-read Collection<int, Message> $replies
 */
class Message extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'chat_id',
        'user_id',
        'external_message_id',
        'direction',
        'type',
        'status',
        'text',
        'payload',
        'reply_to_message_id',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_code',
        'error_message',
        'price',
    ];

    /**
     * Приведения типов.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => MessageDirection::class,
            'status' => MessageStatus::class,
            'type' => MessageType::class,
            'payload' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
