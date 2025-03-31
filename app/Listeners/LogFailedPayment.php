<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class LogFailedPayment
{
    public function handle(PaymentFailed $event): void
    {
        Log::error("Payment failed", [
            'payment_id' => $event->payment->id,
            'error' => $event->errorMessage
        ]);
    }
}
