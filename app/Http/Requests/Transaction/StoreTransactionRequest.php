<?php

namespace App\Http\Requests\Transaction;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
            'type'             => ['required', Rule::enum(TransactionType::class)],
            'amount'           => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'category_id'      => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNull('user_id')
                              ->orWhere('user_id', $this->user()->id);
                    });
                }),
            ],
            'description'      => ['required', 'string', 'max:500'],
            'date'             => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'payment_method'   => ['required', Rule::enum(PaymentMethod::class)],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'            => 'Tipe transaksi wajib diisi.',
            'amount.min'               => 'Jumlah minimal Rp 1.',
            'category_id.exists'       => 'Kategori tidak valid atau bukan milik Anda.',
            'date.before_or_equal'     => 'Tanggal tidak boleh di masa depan.',
            'payment_method.required'  => 'Metode pembayaran wajib diisi.',
        ];
    }
}
