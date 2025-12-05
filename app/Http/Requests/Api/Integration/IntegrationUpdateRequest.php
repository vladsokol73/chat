<?php

namespace App\Http\Requests\Api\Integration;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntegrationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'service' => ['sometimes', 'string', Rule::in(ServiceType::values())],
            'token' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'service.in' => 'The selected service is invalid. Available services: '.implode(', ', ServiceType::values()),
        ];
    }
}
