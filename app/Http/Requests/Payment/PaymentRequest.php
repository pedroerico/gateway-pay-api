<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'method' => ['required', new Enum(PaymentMethodEnum::class)],
            'description' => ['sometimes', 'string', 'max:255'],

            'card.number' => ['required_if:method,credit_card', 'string', 'size:16'],
            'card.holder_name' => ['required_if:method,credit_card', 'string', 'max:255'],
            'card.expiry_month' => ['required_if:method,credit_card', 'string', 'regex:/^0[1-9]|1[0-2]$/'],
            'card.expiry_year' => ['required_if:method,credit_card', 'string', 'regex:/^[0-9]{4}$/',],
            'card.cvv' => ['required_if:method,credit_card', 'string', 'size:3'],
            'card.installments' => ['sometimes', 'integer', 'min:1', 'max:12'],

            'customer.name' => ['required_if:method,credit_card', 'string', 'max:255'],
            'customer.email' => ['required_if:method,credit_card', 'email'],
            'customer.cpf_cnpj' => ['required_if:method,credit_card', 'string', 'size:11,14'],
            'customer.postal_code' => ['required_if:method,credit_card', 'string', 'size:8'],
            'customer.address_number' => ['required_if:method,credit_card', 'string', 'max:10'],
            'customer.phone' => ['required_if:method,credit_card', 'string', 'max:15'],

        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'card' => $this->input('card', []),
            'customer' => $this->input('customer', []),
            'pix' => $this->input('pix', []),
        ]);
    }
}
