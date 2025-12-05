<?php

namespace App\DTO\Chat;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatListCursorPaginatedDto',
    description: 'Cursor-based paginated response with ChatListDto items',
    allOf: [
        new OA\Schema(
            ref: '#/components/schemas/CursorPaginatedDto'
        ),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ChatListDto')
                ),
            ]
        ),
    ]
)]
final class ChatListCursorPaginatedDto {}
