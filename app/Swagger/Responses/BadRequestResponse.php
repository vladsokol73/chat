<?php

namespace App\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'BadRequestResponse',
    description: 'Bad request',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/ApiResponse'),
            new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', nullable: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Invalid request parameters'),
                    new OA\Property(property: 'errors', type: 'object', example: ['field' => 'Validation error message']),
                ]
            ),
        ]
    )
)]
final class BadRequestResponse {}
