<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'     => ['sometimes', 'string', 'min:5', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'username' => ['sometimes', 'string', 'min:5', 'max:20', 'unique:users,username,' . $userId],
            'address'  => ['sometimes', 'string'],
            'role_id'  => ['sometimes', 'exists:roles,id'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $userId],
            'password' => ['sometimes', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
            'status'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min'      => 'Nama minimal 5 karakter.',
            'name.max'      => 'Nama maksimal 50 karakter.',
            'name.regex'    => 'Nama hanya boleh huruf dan spasi.',
            'username.min'  => 'Username minimal 5 karakter.',
            'username.max'  => 'Username maksimal 20 karakter.',
            'username.unique' => 'Username sudah dipakai.',
            // 'gender.in'     => 'Gender harus male atau female.',
            'role.in'       => 'Role harus warga atau admin.',
            'email.email'   => 'Format email tidak valid.',
            'email.unique'  => 'Email sudah terdaftar.',
            'password.min'  => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung minimal 1 huruf kapital dan 1 angka.',
        ];
    }
}