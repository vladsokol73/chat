<?php

namespace App\Http\Requests\Api\Tag;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[OA\Schema(
        schema: 'StoreTagRequest',
        required: ['name'],
        properties: [
            new OA\Property(property: 'name', type: 'string', maxLength: 255),
            new OA\Property(property: 'color', type: 'string', maxLength: 32, nullable: true),
        ],
        type: 'object'
    )]
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ];
    }
}
