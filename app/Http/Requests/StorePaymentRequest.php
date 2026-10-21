<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pay', $this->route('booking'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['amount' => preg_replace('/\D/', '', (string) $this->input('amount'))]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Payment::TYPES))],
            'amount' => ['required', 'integer', 'min:1'],
            'bank_name' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:100'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'bank_name' => 'bank pengirim',
            'account_name' => 'nama pengirim',
            'proof' => 'bukti transfer',
        ];
    }
}
