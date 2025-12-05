<?php

namespace App\Services;

use App\Models\CustomField;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomFieldService
{
    public function __construct() {}

    public function list(): Collection
    {
        return CustomField::query()->orderBy('created_at', 'desc')->get();
    }

    public function paginate(?string $cursor, int $limit, string $direction = 'down'): CursorPaginator
    {
        $limit = max(1, min(200, $limit));

        $query = CustomField::query();

        if ($direction === 'up') {
            $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        } else {
            $direction = 'down';
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        return $query->cursorPaginate(
            perPage: $limit,
            cursor: $cursor
        );
    }

    public function find(string $id): CustomField
    {
        return CustomField::query()->findOrFail($id);
    }

    public function create(array $data): CustomField
    {
        return CustomField::query()->create([
            'key' => (string) $data['key'],
            'name' => (string) $data['name'],
            'entity_type' => (string) $data['entity_type'],
            'type' => (string) $data['type'],
            'options' => $data['options'] ?? null,
            'is_required' => (bool) ($data['is_required'] ?? false),
            'integration_id' => $data['integration_id'] ?? null,
        ]);
    }

    public function update(CustomField $field, array $data): void
    {
        $field->fill([
            'key' => $data['key'] ?? $field->key,
            'name' => $data['name'] ?? $field->name,
            'entity_type' => $data['entity_type'] ?? $field->entity_type,
            'type' => $data['type'] ?? $field->type,
            'options' => $data['options'] ?? $field->options,
            'is_required' => array_key_exists('is_required', $data) ? (bool) $data['is_required'] : $field->is_required,
            'integration_id' => array_key_exists('integration_id', $data) ? $data['integration_id'] : $field->integration_id,
        ]);
        $field->save();
    }

    public function delete(CustomField $field): void
    {
        $field->delete();
    }
}
