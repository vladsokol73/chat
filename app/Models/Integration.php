<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * @property string $id
 * @property string $name
 * @property string|null $external_identifier
 * @property string|null $avatar_url
 * @property ServiceType $service
 * @property string $token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Client> $clients
 * @property-read Collection<int, Chat> $chats
 * @property-read Collection<int, Funnel> $funnels
 */
class Integration extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'service',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'service' => ServiceType::class,
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function funnels(): HasMany
    {
        return $this->hasMany(Funnel::class);
    }

    // Храним token в зашифрованном виде
    public function setTokenAttribute(string $value): void
    {
        $this->attributes['token'] = Crypt::encryptString($value);
    }

    public function getTokenAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }
}
