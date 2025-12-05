<?php

namespace App\Http\Requests\Api\Tag;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'TagIdParam',
    name: 'id',
    description: 'Tag UUID',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
class UpdateTagRequest extends FormRequest
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
        schema: 'UpdateTagRequest',
        properties: [
            new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: true),
            new OA\Property(property: 'color', type: 'string', maxLength: 32, nullable: true),
        ],
        type: 'object'
    )]
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ];
    }
}
