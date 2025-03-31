<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PaymentGatewayException extends Exception
{
    public function __construct(
        string $message = "Payment gateway error",
        int $code = 500,
        ?Throwable $previous = null,
        private ?array $gatewayResponse = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getGatewayResponse(): ?array
    {
        return $this->gatewayResponse;
    }

    public function context(): array
    {
        return [
            'gateway_response' => $this->getGatewayResponse(),
            'error_code' => $this->code,
        ];
    }
}
