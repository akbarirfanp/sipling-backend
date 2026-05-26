<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:5', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'username' => ['required', 'string', 'min:5', 'max:20', 'unique:users,username'],
            'address'  => ['required', 'string'],
            'status'   => ['required', 'boolean'],
            // 'gender'   => ['required', 'in:Pria,Wanita'],
            'role_id'  => ['required', 'uuid', 'exists:roles,id'], // ← diubah
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

            // 'gender.required'   => 'Gender wajib diisi.',
            // 'gender.in'         => 'Gender harus male atau female.',

            'role_id.required'  => 'Role wajib diisi.',
            'role_id.uuid'      => 'Format role tidak valid.',  
            'role_id.exists'    => 'Role tidak ditemukan.',     

            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.regex'    => 'Password harus mengandung minimal 1 huruf kapital dan 1 angka.',
        ];
    }
}