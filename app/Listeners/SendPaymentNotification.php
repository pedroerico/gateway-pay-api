<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use Illuminate\Support\Facades\Log;

class SendPaymentNotification
{
    public function handle(PaymentProcessed $event): void
    {
        $payment = $event->payment;

        Log::info("Payment processed successfully", [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'method' => $payment->method
        ]);
    }
}
