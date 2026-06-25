<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name'  => [
                'required', 'string', 'max:100',
                Rule::unique('categories')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],
            'type'  => ['required', Rule::in(['income', 'expense'])],
            'icon'  => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah digunakan.',
            'type.in'     => 'Tipe harus income atau expense.',
            'color.regex' => 'Format warna tidak valid. Gunakan hex seperti #6366f1.',
        ];
    }
}
