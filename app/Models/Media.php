<?php

namespace App\Models;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $message_id
 * @property MediaType $type
 * @property string $external_id
 * @property string|null $mime_type
 * @property int|null $duration
 * @property string|null $path
 * @property string|null $original_name
 * @property array<string,mixed>|null $sizes
 * @property array<string,mixed>|null $thumbnail
 * @property string|null $title
 * @property string|null $performer
 * @property MediaStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Message $message
 */
final class Media extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'message_id',
        'type',
        'external_id',
        'mime_type',
        'duration',
        'path',
        'original_name',
        'sizes',
        'thumbnail',
        'title',
        'performer',
        'status',
    ];

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'sizes' => 'array',
            'thumbnail' => 'array',
            'status' => MediaStatus::class,
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
