<?php

namespace App\Exceptions;

use App\Models\Payment;
use Throwable;

class GatewayException extends PaymentException
{
    protected array $errors;

    public function __construct(
        string $gatewayName,
        string $message,
        ?Payment $payment = null,
        int $code = 0,
        ?array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct("[{$gatewayName}] {$message}", $payment, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
