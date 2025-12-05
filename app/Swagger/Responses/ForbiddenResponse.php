<?php

namespace App\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'ForbiddenResponse',
    description: 'Forbidden',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/ApiResponse'),
            new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', nullable: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Access denied'),
                ]
            ),
        ]
    )
)]
final class ForbiddenResponse {}
