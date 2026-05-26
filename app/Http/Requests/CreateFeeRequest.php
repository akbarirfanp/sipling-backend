<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateFeeRequest extends FormRequest
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
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'integer', 'min:0'],
            'period'      => ['required', 'in:Bulanan,Tahunan,Mingguan'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama iuran wajib diisi.',
            'amount.required'       => 'Jumlah iuran wajib diisi.',
            'description.required'  => 'Deskripsi wajib diisi.',
            'amount.integer'        => 'Jumlah iuran harus berupa angka bulat.',
            'amount.min'            => 'Jumlah iuran tidak boleh negatif.',
            'period.required'       => 'Periode wajib diisi.',
            'period.in'             => 'Periode hanya boleh: Bulanan, Tahunan, Mingguan',
        ];
    }
}