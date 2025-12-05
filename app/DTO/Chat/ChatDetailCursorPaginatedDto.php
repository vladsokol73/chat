<?php

namespace App\DTO\Chat;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatDetailCursorPaginatedDto',
    description: 'Chat detail with messages paginated via cursor',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'client', ref: '#/components/schemas/ClientDto'),
        new OA\Property(
            property: 'messages',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/MessageDto')
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
            ],
            type: 'object'
        ),
    ]
)]
final class ChatDetailCursorPaginatedDto {}
