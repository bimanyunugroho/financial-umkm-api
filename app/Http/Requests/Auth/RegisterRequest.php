<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $emailRules = ['required', 'string', 'email:rfc,dns', 'unique:users,email', 'max:255'];

        if (config('app.block_disposable_email', true)) {
            $emailRules[] = 'indisposable';
        }

        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => $emailRules,
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'business_name'         => ['required', 'string', 'max:255'],
            'business_type'         => ['required', 'string', 'max:100'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'            => 'Email sudah terdaftar.',
            'email.indisposable'      => 'Gunakan email yang valid, bukan email sementara.',
            'password.confirmed'      => 'Konfirmasi password tidak cocok.',
            'business_name.required'  => 'Nama bisnis wajib diisi.',
        ];
    }
}
