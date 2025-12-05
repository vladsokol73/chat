<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'TranslateRequest_LangParam',
    name: 'lang',
    description: 'Язык перевода',
    in: 'query',
    required: true,
    schema: new OA\Schema(type: 'string', maxLength: 32)
)]
#[OA\Parameter(
    parameter: 'TranslateRequest_TextParam',
    name: 'text',
    description: 'Текст для перевода',
    in: 'query',
    required: true,
    schema: new OA\Schema(type: 'string', maxLength: 8192)
)]
class TranslateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => ['required', 'string', 'max:32'],
            'text' => ['required', 'string', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'lang.required' => 'Language is required',
            'lang.string' => 'Language must be a string',
            'lang.max' => 'Language must not exceed 32 characters',
            'text.required' => 'Text is required',
            'text.string' => 'Text must be a string',
            'text.max' => 'Text must not exceed 8192 characters',
        ];
    }
}
