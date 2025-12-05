<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $external_id
 * @property string $name
 * @property string $phone
 * @property string $avatar
 * @property string|null $comment
 * @property string $integration_id
 * @property string $total_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Integration $integration
 * @property-read Collection<int, Tag> $tags
 * @property-read Collection<int, Chat> $chats
 */
class Client extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'phone',
        'avatar',
        'comment',
        'integration_id',
        'total_price',
    ];

    /**
     * Приведения типов.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    // Удалена привязка к пользователю; назначение ведем на уровне чатов

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'client_tag');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }
}
