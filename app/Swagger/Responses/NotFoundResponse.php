<?php

namespace App\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'NotFoundResponse',
    description: 'Resource not found',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/ApiResponse'),
            new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', nullable: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Resource not found'),
                ]
            ),
        ]
    )
)]
final class NotFoundResponse {}
