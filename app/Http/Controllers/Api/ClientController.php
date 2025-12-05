<?php

namespace App\Http\Controllers\Api;

use App\DTO\Client\UpdateClientDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Client\UpdateClientRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Throwable;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    #[OA\Put(
        path: '/api/clients/{clientId}',
        operationId: 'updateClient',
        summary: 'Обновить данные клиента',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateClientDto')
            )
        ),
        tags: ['Client'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/UpdateClientRequest_ClientIdParam'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client updated successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'phone', type: 'string'),
                                        new OA\Property(property: 'avatar', type: 'string', nullable: true),
                                        new OA\Property(property: 'comment', type: 'string', nullable: true),
                                    ],
                                    type: 'object'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
        ]
    )]
    public function update(UpdateClientRequest $request)
    {
        try {
            $clientId = $request->validated('clientId');
            $client = Client::query()->findOrFail($clientId);

            $dto = UpdateClientDto::fromRequest($request);

            $this->clientService->update($client, $dto);

            $client->refresh();

            return ApiResponse::success(
                data: [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone' => $client->phone,
                    'avatar' => $client->avatar ? Storage::disk('s3')->url($client->avatar) : null,
                    'comment' => $client->comment,
                ],
                message: 'Client updated successfully'
            );
        } catch (Throwable $e) {
            return ApiResponse::notFound('Client not found: '.$e->getMessage());
        }
    }
}
