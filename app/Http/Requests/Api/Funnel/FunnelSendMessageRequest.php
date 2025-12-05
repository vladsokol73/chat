<?php

namespace App\Http\Requests\Api\Funnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class FunnelSendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expectedApiKey = config('app.api-key');

        if (! is_string($expectedApiKey) || $expectedApiKey === '') {
            return false;
        }

        $authorizationHeader = $this->header('Authorization');

        if ($authorizationHeader === null) {
            return false;
        }

        $normalizedHeader = $this->normalizeApiKey($authorizationHeader);

        return hash_equals($expectedApiKey, $normalizedHeader);
    }

    public function rules(): array
    {
        return [
            'user' => ['required', 'string'],
            'message' => ['required', 'string', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'user.required' => 'User identifier is required',
            'message.required' => 'Message text is required',
            'message.max' => 'Message text must not exceed 4096 characters',
        ];
    }

    private function normalizeApiKey(string $value): string
    {
        $value = trim($value);

        if (Str::of($value)->lower()->startsWith('api-key ')) {
            $value = Str::of($value)->substr(8)->toString();
        }

        return trim($value);
    }
}
