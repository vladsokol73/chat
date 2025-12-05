<?php

namespace App\DTO\Pagination;

use OpenApi\Attributes as OA;

/**
 * Универсальная DTO для курсорной пагинации.
 *
 * @template T
 */
#[OA\Schema(
    schema: 'CursorPaginatedDto',
    description: 'Generic cursor-based paginated response wrapper',
    properties: [
        new OA\Property(
            property: 'items',
            description: 'List of items for the current page',
            type: 'array',
            items: new OA\Items(type: 'object')
        ),
        new OA\Property(
            property: 'nextCursor',
            type: 'string',
            example: 'eyJpZCI6Ij...',
            nullable: true
        ),
        new OA\Property(
            property: 'prevCursor',
            type: 'string',
            example: null,
            nullable: true
        ),
    ]
)]
final readonly class CursorPaginatedDto
{
    /**
     * @param  array<T>  $items
     */
    public function __construct(
        public array $items,
        public ?string $nextCursor,
        public ?string $prevCursor,
    ) {}

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'nextCursor' => $this->nextCursor,
            'prevCursor' => $this->prevCursor,
        ];
    }
}
