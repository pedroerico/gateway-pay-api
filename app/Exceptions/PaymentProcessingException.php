<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PaymentProcessingException extends Exception
{
    public function __construct(
        string $message = "Payment processing failed",
        int $code = 500,
        ?Throwable $previous = null,
        private ?string $paymentId = null,
        private ?string $gateway = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function context(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'gateway' => $this->gateway,
            'error_code' => $this->code,
        ];
    }
}
