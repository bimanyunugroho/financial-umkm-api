<?php

namespace App\Http\Requests\Report;

use App\Enums\ReportPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportReportRequest extends FormRequest
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
            'format'    => ['required', Rule::in(['pdf', 'xlsx'])],
            'period'    => ['sometimes', Rule::enum(ReportPeriod::class)],
            'year'      => ['sometimes', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'month'     => ['sometimes', 'integer', 'min:1', 'max:12'],
            'date_from' => ['required_if:period,custom', 'date_format:Y-m-d'],
            'date_to'   => ['required_if:period,custom', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'format.required' => 'Format export wajib diisi (pdf atau xlsx).',
            'format.in'       => 'Format harus pdf atau xlsx.',
        ];
    }
}
