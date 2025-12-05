<?php

namespace App\Http\Requests\Api\Integration;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntegrationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', Rule::in(ServiceType::values())],
            'token' => ['required', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'service.in' => 'The selected service is invalid. Available services: '.implode(', ', ServiceType::values()),
        ];
    }
}
