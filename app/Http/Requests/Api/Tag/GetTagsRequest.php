<?php

namespace App\Http\Requests\Api\Tag;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'GetTags_CursorParam',
    name: 'cursor',
    description: 'Cursor for pagination',
    in: 'query',
    schema: new OA\Schema(type: 'string', nullable: true)
)]
#[OA\Parameter(
    parameter: 'GetTags_LimitParam',
    name: 'limit',
    description: 'Items per page (1..200)',
    in: 'query',
    schema: new OA\Schema(type: 'integer', default: 50)
)]
#[OA\Parameter(
    parameter: 'GetTags_DirectionParam',
    name: 'direction',
    description: 'Pagination direction',
    in: 'query',
    schema: new OA\Schema(type: 'string', default: 'down', enum: ['up', 'down'])
)]
class GetTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursor' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'direction' => ['nullable', 'in:up,down'],
        ];
    }
}
