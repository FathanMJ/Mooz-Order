<?php

namespace App\Http\Requests;

class ResetPasswordRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:pengguna,email',
            'otp' => 'required|string|size:4',
            'new_password' => 'required|string|min:6',
            'new_password_confirmation' => 'required|same:new_password'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar',
            'otp.required' => 'Kode OTP harus diisi',
            'otp.size' => 'Kode OTP harus 4 digit',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi',
            'new_password_confirmation.same' => 'Konfirmasi password baru tidak cocok'
        ];
    }
}
