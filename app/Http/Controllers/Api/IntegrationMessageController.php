<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Integration\IntegrationWebhookRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Integration\IntegrationMessageService;
use OpenApi\Attributes as OA;
use Throwable;

class IntegrationMessageController extends Controller
{
    public function __construct(
        private readonly IntegrationMessageService $integrationMessageService
    ) {}

    /**
     * @throws Throwable
     */
    #[OA\Post(
        path: '/webhook/integration/{integrationId}',
        operationId: 'integrationWebhook',
        summary: 'Унифицированный webhook для интеграций',
        tags: ['Integration'],
        parameters: [
            new OA\Parameter(
                name: 'integrationId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
        ]
    )]
    public function integrationWebhook(IntegrationWebhookRequest $request)
    {
        $this->integrationMessageService->handleWebhook($request);

        return ApiResponse::success();
    }
}
