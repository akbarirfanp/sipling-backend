<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name'     => ['required', 'string', 'min:5', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'username' => ['required', 'string', 'min:5', 'max:20', 'unique:users,username'],
            'address'  => ['required', 'string'],
            'gender'   => ['required', 'in:male,female'],
            'role'     => ['required', 'in:warga,admin'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'name.min'          => 'Nama minimal 5 karakter.',
            'name.max'          => 'Nama maksimal 50 karakter.',
            'name.regex'        => 'Nama hanya boleh huruf dan spasi.',

            'username.required' => 'Username wajib diisi.',
            'username.min'      => 'Username minimal 5 karakter.',
            'username.max'      => 'Username maksimal 20 karakter.',
            'username.unique'   => 'Username sudah dipakai.',

            'address.required'  => 'Alamat wajib diisi.',

            'gender.required'   => 'Gender wajib diisi.',
            'gender.in'         => 'Gender harus male atau female.',

            'role.required'     => 'Role wajib diisi.',
            'role.in'           => 'Role harus warga atau admin.',

            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.regex'    => 'Password harus mengandung minimal 1 huruf kapital dan 1 angka.',
        ];
    }
}