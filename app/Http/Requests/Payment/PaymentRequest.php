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
            'external_id' => ['sometimes', 'integer', 'required_without:customer'],

            'customer' => ['required_without:external_id', 'array'],
            'customer.name' => ['string', 'max:255'],
            'customer.email' => ['email'],
            'customer.cpf' => ['required_with:customer', 'string', 'size:11,14'],
            'customer.phone' => ['string', 'max:15'],

            'card.number' => ['required_if:method,credit_card', 'string', 'size:16'],
            'card.holder_name' => ['required_if:method,credit_card', 'string', 'max:255'],
            'card.expiry_month' => ['required_if:method,credit_card', 'string', 'regex:/^0[1-9]|1[0-2]$/'],
            'card.expiry_year' => ['required_if:method,credit_card', 'string', 'regex:/^[0-9]{4}$/',],
            'card.cvv' => ['required_if:method,credit_card', 'string', 'size:3'],
            'card.installments' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'card.name' => ['required_if:method,credit_card', 'string', 'max:255'],
            'card.email' => ['required_if:method,credit_card', 'email'],
            'card.cpf' => ['required_if:method,credit_card', 'string', 'size:11,14'],
            'card.postal_code' => ['required_if:method,credit_card', 'string', 'size:8'],
            'card.address_number' => ['required_if:method,credit_card', 'string', 'max:10'],
            'card.phone' => ['required_if:method,credit_card', 'string', 'max:15'],

            'address.zip_code' => ['required_if:method,boleto', 'string', 'max:8'],
            'address.street' => ['required_if:method,boleto', 'string'],
            'address.number' => ['required_if:method,boleto', 'string'],
            'address.neighborhood' => ['required_if:method,boleto', 'string'],
            'address.city' => ['required_if:method,boleto', 'string'],
            'address.state' => ['required_if:method,boleto', 'string', 'max:2'],
        ];
    }
}
