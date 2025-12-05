<?php

namespace App\Http\Requests\Api\Integration;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationWebhookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'integrationId' => $this->route('integrationId'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integrationId' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'integrationId.required' => 'Integration ID is required',
            'integrationId.string' => 'Integration ID must be a string',
        ];
    }
}
