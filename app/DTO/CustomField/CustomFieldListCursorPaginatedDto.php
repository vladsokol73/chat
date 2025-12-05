<?php

namespace App\DTO\CustomField;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CustomFieldListCursorPaginatedDto',
    description: 'Cursor-based paginated response with CustomFieldDto items',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/CursorPaginatedDto'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CustomFieldDto')
                ),
            ]
        ),
    ]
)]
final class CustomFieldListCursorPaginatedDto {}
