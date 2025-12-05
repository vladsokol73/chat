<?php

namespace App\Swagger\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'ValidationErrorResponse',
    description: 'Validation error',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/ApiResponse'),
            new OA\Schema(
                properties: [
                    new OA\Property(property: 'data', nullable: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
                    new OA\Property(
                        property: 'errors',
                        type: 'object',
                        example: [
                            'field1' => ['The field1 is required.'],
                            'field2' => ['The field2 must be an integer.'],
                        ]
                    ),
                ]
            ),
        ]
    )
)]
final class ValidationErrorResponse {}
