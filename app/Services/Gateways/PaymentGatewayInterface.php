<?php

namespace App\Services\Gateways;

use App\DTO\Payment\PaymentDTO;
use App\DTO\Payment\Response\PaymentResponseDTO;

interface PaymentGatewayInterface
{
    public function processPayment(PaymentDTO $paymentDTO): PaymentResponseDTO;
    public function getName(): string;
    public function supportsMethod(string $method): bool;
}
