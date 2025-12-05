<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Документация API для проекта GChat',
    title: 'GChat API'
)]
final class ApiDocs {}
