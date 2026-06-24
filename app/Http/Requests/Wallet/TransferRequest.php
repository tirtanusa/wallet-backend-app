<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_email' => ['required', 'email', 'exists:users,email'],
            'amount'          => ['required', 'numeric', 'min:10000'],
            'description'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_email.required' => 'Email penerima wajib diisi.',
            'recipient_email.email'    => 'Format email penerima tidak valid.',
            'recipient_email.exists'   => 'Pengguna dengan email tersebut tidak ditemukan.',
            'amount.required'          => 'Jumlah transfer wajib diisi.',
            'amount.numeric'           => 'Jumlah transfer harus berupa angka.',
            'amount.min'               => 'Jumlah transfer minimal Rp10.000.',
        ];
    }
}