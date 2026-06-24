<?php

namespace App\Http\Requests\Transaction;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'type'             => ['sometimes', Rule::enum(TransactionType::class)],
            'amount'           => ['sometimes', 'numeric', 'min:1', 'max:999999999999'],
            'category_id'      => [
                'sometimes', 'uuid',
                Rule::exists('categories', 'id')->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNull('user_id')
                              ->orWhere('user_id', $this->user()->id);
                    });
                }),
            ],
            'description'      => ['sometimes', 'string', 'max:500'],
            'date'             => ['sometimes', 'date_format:Y-m-d', 'before_or_equal:today'],
            'payment_method'   => ['sometimes', Rule::enum(PaymentMethod::class)],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min'           => 'Jumlah minimal Rp 1.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        ];
    }
}
