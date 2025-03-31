<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->clearPaymentCache($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->clearPaymentCache($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->clearPaymentCache($payment);
    }

    private function clearPaymentCache(Payment $payment): void
    {
        Cache::forget("payment:{$payment->id}");
    }
}
