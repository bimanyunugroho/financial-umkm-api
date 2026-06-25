<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'string', 'email:rfc,dns', "unique:users,email,{$userId}", 'max:255'],
            'password'      => ['sometimes', Password::min(8), 'confirmed'],
            'business_name' => ['sometimes', 'string', 'max:255'],
            'business_type' => ['sometimes', 'string', 'max:100'],
            'phone'         => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'       => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'       => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
