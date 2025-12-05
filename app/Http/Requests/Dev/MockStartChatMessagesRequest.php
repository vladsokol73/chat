<?php

namespace App\Http\Requests\Dev;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MockStartChatMessagesRequest',
    properties: [
        new OA\Property(
            property: 'text',
            description: 'Текст создаваемых сообщений',
            type: 'string',
            example: 'Тестовое сообщение'
        ),
        new OA\Property(
            property: 'type',
            description: 'Тип сообщения',
            type: 'string',
            enum: ['text', 'image', 'system'],
            example: 'text'
        ),
        new OA\Property(
            property: 'count',
            description: 'Сколько сообщений создать (опционально)',
            type: 'integer',
            example: 5
        ),
    ],
    type: 'object'
)]
class MockStartChatMessagesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'chatId' => $this->route('chatId'),
            'text' => $this->input('text', 'Mock message'),
            'count' => $this->input('count', 5),
            'type' => $this->input('type', 'text'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chatId' => ['required', 'uuid'],
            'text' => ['nullable', 'string', 'max:2000'],
            'type' => ['nullable', 'string', 'in:text,image,system'],
            'count' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'chatId.required' => 'Chat id is required',
            'chatId.uuid' => 'Chat id must be a UUID',
            'text.string' => 'Text must be a string',
            'text.max' => 'Text is too long (max 2000 chars)',
            'type.in' => 'Type must be one of: text, image, system',
            'count.integer' => 'Count must be an integer',
            'count.min' => 'Count must be at least 1',
            'count.max' => 'Count must be at most 1000',
        ];
    }
}
