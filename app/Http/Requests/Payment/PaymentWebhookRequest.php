<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'in:PAYMENT_RECEIVED,PAYMENT_REFUNDED,PAYMENT_FAILED'],
            'payment' => ['required', 'array'],
            'payment.id' => ['required', 'string'],
            'payment.status' => ['required', 'string', 'in:CONFIRMED,PENDING,REFUNDED,CANCELLED'],
            'payment.value' => ['required', 'numeric'],
            'payment.netValue' => ['sometimes', 'numeric'],
            'payment.billingType' => ['required', 'string', 'in:CREDIT_CARD,BOLETO,PIX'],
            'payment.invoiceUrl' => ['sometimes', 'string', 'url'],
            'payment.pixQrCode' => ['sometimes', 'string'],
            'payment.pixKey' => ['sometimes', 'string'],
        ];
    }
}
