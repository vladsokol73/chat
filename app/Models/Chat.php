<?php

namespace App\Models;

use App\Enums\ChatStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $client_id
 * @property string|null $integration_id
 * @property string|null $assigned_user_id
 * @property string|null $external_id
 * @property string $channel
 * @property string $status
 * @property string|null $last_message_id
 * @property Carbon|null $last_message_at
 * @property int $unread_count
 * @property string|null $conversation_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Client $client
 * @property-read Integration|null $integration
 * @property-read Message|null $lastMessage
 * @property-read User|null $assignedUser
 * @property-read Collection<int, Message> $messages
 */
class Chat extends Model
{
    protected $table = 'chats';

    use HasUuids;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'integration_id',
        'assigned_user_id',
        'external_id',
        'channel',
        'status',
        'last_message_id',
        'last_message_at',
        'unread_count',
        'conversation_id',
    ];

    /**
     * Приведения типов.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'unread_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Scope: all chats except blocked.
     */
    public function scopeExceptBlocked(Builder $query): Builder
    {
        return $query->where('status', '!=', ChatStatus::BLOCKED->value);
    }

    /**
     * Scope: only chats with unread messages.
     */
    public function scopeOnlyNew(Builder $query): Builder
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Scope: only chats assigned to specific user.
     */
    public function scopeOnlyMy(Builder $query, string $userId): Builder
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * Scope: only blocked chats.
     */
    public function scopeOnlyBlocked(Builder $query): Builder
    {
        return $query->where('status', ChatStatus::BLOCKED->value);
    }

    /**
     * Scope: apply high-level sort/filter preset.
     *
     * - all: все кроме blocked
     * - new: только с непрочитанными
     * - my: только назначенные указанному пользователю
     * - blocked: только заблокированные
     */
    public function scopeSort(Builder $query, string $sort = 'all', ?string $userId = null): Builder
    {
        switch ($sort) {
            case 'new':
                return $query->where('unread_count', '>', 0);

            case 'my':
                if ($userId === null) {
                    // нет авторизованного пользователя — пустой набор
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('assigned_user_id', $userId);

            case 'blocked':
                return $query->where('status', ChatStatus::BLOCKED->value);

            case 'all':
            default:
                return $query->where('status', '!=', ChatStatus::BLOCKED->value);
        }
    }
}
