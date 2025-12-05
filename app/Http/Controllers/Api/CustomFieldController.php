<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomField\GetCustomFieldsRequest;
use App\Http\Requests\Api\CustomField\StoreCustomFieldRequest;
use App\Http\Requests\Api\CustomField\UpdateCustomFieldRequest;
use App\Http\Responses\ApiResponse;
use App\Services\CustomFieldService;
use App\Transformers\Mappers\CustomFieldMapper;
use OpenApi\Attributes as OA;

class CustomFieldController extends Controller
{
    public function __construct(
        private readonly CustomFieldService $service,
        private readonly CustomFieldMapper $mapper,
    ) {}

    #[OA\Get(
        path: '/custom-fields',
        operationId: 'listCustomFields',
        summary: 'Список кастомных полей',
        tags: ['CustomField'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/GetCustomFields_CursorParam'),
            new OA\Parameter(ref: '#/components/parameters/GetCustomFields_LimitParam'),
            new OA\Parameter(ref: '#/components/parameters/GetCustomFields_DirectionParam'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ok',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/CustomFieldListCursorPaginatedDto'
                                ),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(GetCustomFieldsRequest $request)
    {
        $cursor = $request->query('cursor');
        $limit = (int) $request->query('limit', 50);
        $direction = $request->query('direction', 'down');

        $paginator = $this->service->paginate($cursor, $limit, $direction);
        $items = array_map(
            fn ($m) => $this->mapper->map($m)->toArray(),
            $paginator->items()
        );

        return ApiResponse::success([
            'items' => $items,
            'nextCursor' => $paginator->nextCursor()?->encode(),
            'prevCursor' => $paginator->previousCursor()?->encode(),
        ]);
    }

    #[OA\Post(
        path: '/custom-fields',
        operationId: 'createCustomField',
        summary: 'Создать кастомное поле',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/StoreCustomFieldRequest')
            )
        ),
        tags: ['CustomField'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/CustomFieldDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
        ]
    )]
    public function store(StoreCustomFieldRequest $request)
    {
        $field = $this->service->create($request->validated());

        return ApiResponse::created($this->mapper->map($field)->toArray());
    }

    #[OA\Get(
        path: '/custom-fields/{id}',
        operationId: 'getCustomField',
        summary: 'Получить кастомное поле',
        tags: ['CustomField'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/CustomFieldIdParam')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ok',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/CustomFieldDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function show(UpdateCustomFieldRequest $request, string $id)
    {
        $field = $this->service->find($id);

        return ApiResponse::success($this->mapper->map($field)->toArray());
    }

    #[OA\Put(
        path: '/custom-fields/{id}',
        operationId: 'updateCustomField',
        summary: 'Обновить кастомное поле',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateCustomFieldRequest')
            )
        ),
        tags: ['CustomField'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/CustomFieldIdParam')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ok',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/CustomFieldDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function update(UpdateCustomFieldRequest $request, string $id)
    {
        $field = $this->service->find($id);
        $this->service->update($field, $request->validated());
        $field->refresh();

        return ApiResponse::success($this->mapper->map($field)->toArray());
    }

    #[OA\Delete(
        path: '/custom-fields/{id}',
        operationId: 'deleteCustomField',
        summary: 'Удалить кастомное поле',
        tags: ['CustomField'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/CustomFieldIdParam')],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function destroy(UpdateCustomFieldRequest $request, string $id)
    {
        $field = $this->service->find($id);
        $this->service->delete($field);

        return ApiResponse::deleted();
    }
}
