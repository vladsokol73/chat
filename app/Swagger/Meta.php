<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Meta',
    properties: [
        new OA\Property(property: 'next_cursor', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'prev_cursor', type: 'integer', example: null, nullable: true),
    ],
    type: 'object'
)]
final class Meta {}
