<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'seller' && $this->user()?->seller !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => is_string($this->amount)
                ? str_replace([' ', ','], ['', '.'], trim($this->amount))
                : $this->amount,
            'account_holder' => trim((string) $this->account_holder),
            'account_number' => preg_replace('/\s+/', '', (string) $this->account_number),
        ]);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1000', 'max:99999999.99', 'decimal:0,2'],
            'payment_method' => ['required', Rule::in(['ccp', 'baridimob', 'bank_transfer'])],
            'account_holder' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-\/]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum withdrawal amount is 1,000 DA.',
            'account_number.regex' => 'The account number may only contain letters, numbers, dashes, and slashes.',
        ];
    }
}
