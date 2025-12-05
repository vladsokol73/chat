<?php

namespace App\Http\Requests\Api\CustomField;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

class StoreCustomFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[OA\Schema(
        schema: 'StoreCustomFieldRequest',
        required: ['key', 'name', 'entity_type', 'type'],
        properties: [
            new OA\Property(property: 'key', type: 'string', maxLength: 255),
            new OA\Property(property: 'name', type: 'string', maxLength: 255),
            new OA\Property(property: 'entity_type', type: 'string', maxLength: 64),
            new OA\Property(property: 'type', type: 'string', maxLength: 64),
            new OA\Property(property: 'options', type: 'array', items: new OA\Items, nullable: true),
            new OA\Property(property: 'is_required', type: 'boolean', nullable: true),
            new OA\Property(property: 'integration_id', type: 'string', format: 'uuid', nullable: true),
        ],
        type: 'object'
    )]
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', 'max:64'],
            'options' => ['nullable', 'array'],
            'is_required' => ['nullable', 'boolean'],
            'integration_id' => ['nullable', 'uuid'],
        ];
    }
}
