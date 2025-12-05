<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $custom_field_id
 * @property string $entity_type
 * @property string $entity_id
 * @property string|null $value_text
 * @property array|null $value_json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CustomField $field
 */
class CustomFieldValue extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'custom_field_id',
        'entity_type',
        'entity_id',
        'value_text',
        'value_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_json' => 'array',
        ];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }
}
