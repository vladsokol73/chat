<?php

namespace App\DTO\Tag;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TagListCursorPaginatedDto',
    description: 'Cursor-based paginated response with TagDto items',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/CursorPaginatedDto'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/TagDto')
                ),
            ]
        ),
    ]
)]
final class TagListCursorPaginatedDto {}
