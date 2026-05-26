<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateBillRequest extends FormRequest
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
            'due_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'fee_id'   => ['required', 'uuid', 'exists:fees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'fee_id.required'          => 'Jenis iuran perlu diisi.',
            'due_date.required'        => 'Tanggal jatuh tempo perlu diisi.',
            'due_date.date'            => 'Tanggal jatuh tempo harus berupa tanggal yang valid.',
            'due_date.date_format'     => 'Format tanggal jatuh tempo harus YYYY-MM-DD.',
            'due_date.after_or_equal'  => 'Tanggal jatuh tempo tidak boleh sebelum hari ini.',
        ];
    }
}
