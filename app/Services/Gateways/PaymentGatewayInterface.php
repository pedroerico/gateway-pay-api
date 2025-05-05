<?php

namespace App\Services\Gateways;

use App\DTO\Payment\CustomerDTO;
use App\DTO\Payment\PaymentDTO;
use App\DTO\Payment\Response\GatewayCustomerResponseDTO;
use App\DTO\Payment\Response\PaymentResponseDTO;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function processPayment(PaymentDTO $paymentDTO, Payment $payment): PaymentResponseDTO;
    public function createCustomer(CustomerDTO $customerDTO, string|int|null $externalReference = null): GatewayCustomerResponseDTO;
    public function getGatewayId(): string;
    public function getName(): string;
    public function supportsMethod(string $method): bool;
}
