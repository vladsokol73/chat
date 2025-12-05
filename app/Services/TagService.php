<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function __construct() {}

    public function list(): Collection
    {
        return Tag::query()->orderBy('name')->get();
    }

    public function paginate(?string $cursor, int $limit, string $direction = 'down'): CursorPaginator
    {
        $limit = max(1, min(200, $limit));

        $query = Tag::query();

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

    public function find(string $id): Tag
    {
        return Tag::query()->findOrFail($id);
    }

    public function create(string $name, ?string $color = null): Tag
    {
        return Tag::query()->create([
            'name' => $name,
            'color' => $color,
        ]);
    }

    public function update(Tag $tag, array $data): void
    {
        $tag->fill([
            'name' => $data['name'] ?? $tag->name,
            'color' => $data['color'] ?? $tag->color,
        ]);
        $tag->save();
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}
