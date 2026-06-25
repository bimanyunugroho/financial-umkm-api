<?php

namespace App\Http\Requests\Ai;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AiAskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Pertanyaan wajib diisi.',
            'question.min'      => 'Pertanyaan minimal 5 karakter.',
            'question.max'      => 'Pertanyaan maksimal 500 karakter.',
        ];
    }
}
