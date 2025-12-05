<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'GetChatsRequest_CursorParam',
    name: 'cursor',
    description: 'Курсор пагинации (base64-json: {"ts":"ISO","id":"uuid"})',
    in: 'query',
    schema: new OA\Schema(type: 'string', nullable: true)
)]
#[OA\Parameter(
    parameter: 'GetChatsRequest_LimitParam',
    name: 'limit',
    description: 'Количество чатов',
    in: 'query',
    schema: new OA\Schema(type: 'integer', default: 50)
)]
#[OA\Parameter(
    parameter: 'GetChatsRequest_DirectionParam',
    name: 'direction',
    description: 'Направление пагинации',
    in: 'query',
    schema: new OA\Schema(type: 'string', default: 'down', enum: ['up', 'down'])
)]
#[OA\Parameter(
    parameter: 'GetChatsRequest_SortParam',
    name: 'sort',
    description: 'Фильтр чатов: all (все кроме blocked), new (непрочитанные), my (мои), blocked (заблокированные)',
    in: 'query',
    schema: new OA\Schema(type: 'string', default: 'all', enum: ['all', 'new', 'my', 'blocked'])
)]
class GetChatsRequest extends FormRequest
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
            'sort' => ['nullable', 'in:all,new,my,blocked'],
        ];
    }

    public function messages(): array
    {
        return [
            'limit.max' => 'Limit must be at most 200',
            'direction.in' => 'Direction must be either up or down',
            'sort.in' => 'Sort must be one of: all, new, my, blocked',
        ];
    }
}
