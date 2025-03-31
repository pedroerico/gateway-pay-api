<?php

namespace App\Exceptions;

use App\Models\Payment;
use Throwable;

class GatewayException extends PaymentException
{

    public function __construct(
        string $gatewayName,
        string $message,
        ?Payment $payment = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct("[{$gatewayName}] {$message}", $payment, $code, $previous);
    }
}
