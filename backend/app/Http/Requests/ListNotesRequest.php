<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The list endpoint accepts no fields. The request still runs validation,
     * so every API method has an explicit validation boundary.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
