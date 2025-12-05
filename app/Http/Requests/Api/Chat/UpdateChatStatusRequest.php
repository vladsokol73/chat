<?php

namespace App\Http\Requests\Api\Chat;

use App\Enums\ChatStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'UpdateChatStatusRequest_ChatIdParam',
    name: 'chatId',
    description: 'ID чата',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class UpdateChatStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(ChatStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'chatId.required' => 'Chat id is required',
            'chatId.uuid' => 'Chat id must be a UUID',
            'status.required' => 'Status is required',
            'status.enum' => 'Status must be one of: auto, manual, blocked',
        ];
    }
}
