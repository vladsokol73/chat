<?php

namespace App\Http\Requests\Api\CustomField;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'CustomFieldIdParam',
    name: 'id',
    description: 'Custom field UUID',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class UpdateCustomFieldRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    #[OA\Schema(
        schema: 'UpdateCustomFieldRequest',
        properties: [
            new OA\Property(property: 'key', type: 'string', maxLength: 255, nullable: true),
            new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: true),
            new OA\Property(property: 'entity_type', type: 'string', maxLength: 64, nullable: true),
            new OA\Property(property: 'type', type: 'string', maxLength: 64, nullable: true),
            new OA\Property(property: 'options', type: 'array', items: new OA\Items, nullable: true),
            new OA\Property(property: 'is_required', type: 'boolean', nullable: true),
            new OA\Property(property: 'integration_id', type: 'string', format: 'uuid', nullable: true),
        ],
        type: 'object'
    )]
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'key' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:64'],
            'type' => ['nullable', 'string', 'max:64'],
            'options' => ['nullable', 'array'],
            'is_required' => ['nullable', 'boolean'],
            'integration_id' => ['nullable', 'uuid'],
        ];
    }
}
