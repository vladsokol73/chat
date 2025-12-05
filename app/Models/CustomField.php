<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $key
 * @property string $name
 * @property string $entity_type
 * @property string $type
 * @property array|null $options
 * @property bool $is_required
 * @property string|null $integration_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, CustomFieldValue> $values
 */
class CustomField extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'name',
        'entity_type',
        'type',
        'options',
        'is_required',
        'integration_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
