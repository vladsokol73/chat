<?php

namespace App\Http\Requests\Api\Client;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'UpdateClientRequest_ClientIdParam',
    name: 'clientId',
    description: 'UUID клиента',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class UpdateClientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['clientId' => $this->route('clientId')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clientId' => ['required', 'uuid'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'image', 'max:10240'], // 10MB max
            'comment' => ['nullable', 'string', 'max:512'],
        ];
    }

    public function messages(): array
    {
        return [
            'clientId.required' => 'Client id is required',
            'clientId.uuid' => 'Client id must be a valid UUID',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name must not exceed 255 characters',
            'phone.string' => 'Phone must be a string',
            'phone.max' => 'Phone must not exceed 255 characters',
            'avatar.file' => 'Avatar must be a file',
            'avatar.image' => 'Avatar must be an image',
            'avatar.max' => 'Avatar must not exceed 10MB',
            'comment.string' => 'Comment must be a string',
            'comment.max' => 'Comment must not exceed 512 characters',
        ];
    }
}
