<?php

namespace App\Http\Requests\Api\Funnel;

use Illuminate\Foundation\Http\FormRequest;

class FunnelCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'integration_id' => ['required', 'uuid', 'exists:integrations,id'],
            'api_key' => ['required', 'string', 'max:2048'],
        ];
    }
}
