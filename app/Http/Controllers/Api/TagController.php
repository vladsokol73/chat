<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tag\GetTagsRequest;
use App\Http\Requests\Api\Tag\StoreTagRequest;
use App\Http\Requests\Api\Tag\UpdateTagRequest;
use App\Http\Responses\ApiResponse;
use App\Services\TagService;
use App\Transformers\Mappers\TagMapper;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    public function __construct(
        private readonly TagService $tagService,
        private readonly TagMapper $tagMapper,
    ) {}

    #[OA\Get(
        path: '/tags',
        operationId: 'listTags',
        summary: 'Список тегов',
        tags: ['Tag'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/GetTags_CursorParam'),
            new OA\Parameter(ref: '#/components/parameters/GetTags_LimitParam'),
            new OA\Parameter(ref: '#/components/parameters/GetTags_DirectionParam'),
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
                                    ref: '#/components/schemas/TagListCursorPaginatedDto'
                                ),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(GetTagsRequest $request)
    {
        $cursor = $request->query('cursor');
        $limit = (int) $request->query('limit', 50);
        $direction = $request->query('direction', 'down');

        $paginator = $this->tagService->paginate($cursor, $limit, $direction);
        $items = array_map(
            fn ($t) => $this->tagMapper->map($t)->toArray(),
            $paginator->items()
        );

        return ApiResponse::success([
            'items' => $items,
            'nextCursor' => $paginator->nextCursor()?->encode(),
            'prevCursor' => $paginator->previousCursor()?->encode(),
        ]);
    }

    #[OA\Post(
        path: '/tags',
        operationId: 'createTag',
        summary: 'Создать тег',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/StoreTagRequest')
            )
        ),
        tags: ['Tag'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/TagDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
        ]
    )]
    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->create(
            name: (string) $request->validated('name'),
            color: $request->validated('color'),
        );

        return ApiResponse::created($this->tagMapper->map($tag)->toArray());
    }

    #[OA\Get(
        path: '/tags/{id}',
        operationId: 'getTag',
        summary: 'Получить тег',
        tags: ['Tag'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/TagIdParam')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ok',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/TagDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function show(UpdateTagRequest $request, string $id)
    {
        $tag = $this->tagService->find($id);

        return ApiResponse::success($this->tagMapper->map($tag)->toArray());
    }

    #[OA\Put(
        path: '/tags/{id}',
        operationId: 'updateTag',
        summary: 'Обновить тег',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/UpdateTagRequest')
            )
        ),
        tags: ['Tag'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/TagIdParam')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ok',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', ref: '#/components/schemas/TagDto'),
                        ]),
                    ]
                )
            ),
            new OA\Response(ref: '#/components/responses/ValidationErrorResponse', response: 422),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function update(UpdateTagRequest $request, string $id)
    {
        $tag = $this->tagService->find($id);
        $this->tagService->update($tag, $request->validated());
        $tag->refresh();

        return ApiResponse::success($this->tagMapper->map($tag)->toArray());
    }

    #[OA\Delete(
        path: '/tags/{id}',
        operationId: 'deleteTag',
        summary: 'Удалить тег',
        tags: ['Tag'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/TagIdParam')],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(ref: '#/components/responses/NotFoundResponse', response: 404),
        ]
    )]
    public function destroy(UpdateTagRequest $request, string $id)
    {
        $tag = $this->tagService->find($id);
        $this->tagService->delete($tag);

        return ApiResponse::deleted();
    }
}
