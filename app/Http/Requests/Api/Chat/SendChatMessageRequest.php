<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
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
            'messageId' => ['required', 'uuid'],
            'text' => ['required', 'string', 'max:4096'],
            'media_ids' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'chatId.required' => 'Chat id is required',
            'chatId.uuid' => 'Chat id must be a valid UUID',
            'messageId.required' => 'Message id is required',
            'messageId.uuid' => 'Message id must be a valid UUID',
            'text.required' => 'Text is required',
            'text.max' => 'Text must not exceed 4096 characters',
            'media_ids.array' => 'Media ids must be an array',
            'media_ids.*.uuid' => 'Media id must be a valid UUID',
        ];
    }
}
