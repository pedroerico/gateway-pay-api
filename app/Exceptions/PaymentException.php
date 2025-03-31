<?php

namespace App\Exceptions;

use App\Models\Payment;
use Exception;
use Throwable;

class PaymentException extends Exception
{
    private ?Payment $payment;

    public function __construct(string $message, ?Payment $payment = null, int $code = 0, ?Throwable $previous = null)
    {
        $this->payment = $payment;
        parent::__construct($message, $code, $previous);
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }
}
