<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'MarkChatMessagesReadRequest_ChatIdParam',
    name: 'chatId',
    description: 'ID чата',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class MarkChatMessagesReadRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'chatId.required' => 'Chat id is required',
            'chatId.uuid' => 'Chat id must be a UUID',
        ];
    }
}
