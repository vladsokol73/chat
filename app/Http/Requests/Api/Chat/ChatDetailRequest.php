<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'ChatDetailRequest_ChatIdParam',
    name: 'chatId',
    description: 'ID чата',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class ChatDetailRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['chatId' => $this->route('chatId')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chatId' => ['required', 'uuid'],
            'cursor' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'direction' => ['nullable', 'in:up,down'],
        ];
    }

    public function messages(): array
    {
        return [
            'chatId.required' => 'Chat id is required',
            'chatId.uuid' => 'Chat id must be a UUID',
            'limit.max' => 'Limit must be at most 200',
            'direction.in' => 'Direction must be either up or down',
        ];
    }
}
