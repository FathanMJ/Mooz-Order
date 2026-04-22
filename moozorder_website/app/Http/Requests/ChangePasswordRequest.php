<?php

namespace App\Http\Requests;

class ChangePasswordRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|different:current_password',
            'new_password_confirmation' => 'required|same:new_password'
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
            'new_password.different' => 'Password baru harus berbeda dengan password saat ini',
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi',
            'new_password_confirmation.same' => 'Konfirmasi password baru tidak cocok'
        ];
    }
} 