<?php

namespace App\Http\Requests\Api\Funnel;

use Illuminate\Foundation\Http\FormRequest;

class FunnelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'integration_id' => ['sometimes', 'uuid', 'exists:integrations,id'],
            'api_key' => ['sometimes', 'string', 'max:2048', 'nullable'],
        ];
    }
}
