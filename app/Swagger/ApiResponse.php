<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiResponse',
    properties: [
        new OA\Property(
            property: 'success',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Request successful'
        ),
        new OA\Property(
            property: 'data',
            nullable: true
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'meta',
            ref: '#/components/schemas/Meta',
            nullable: true
        ),
    ],
    type: 'object'
)]
final class ApiResponse {}
