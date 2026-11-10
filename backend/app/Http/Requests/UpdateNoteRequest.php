<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Укажите заголовок.',
            'title.string' => 'Заголовок должен быть строкой.',
            'title.max' => 'Заголовок не длиннее 255 символов.',
            'body.required' => 'Укажите текст заметки.',
            'body.string' => 'Текст заметки должен быть строкой.',
            'body.max' => 'Текст заметки не длиннее 10000 символов.',
        ];
    }
}
