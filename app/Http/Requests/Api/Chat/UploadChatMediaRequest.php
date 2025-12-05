<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UploadChatMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // если есть авторизация по guard'у — можно дополнительно проверить
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
            ],
        ];
    }
}
