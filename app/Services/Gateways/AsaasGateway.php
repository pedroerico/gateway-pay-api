<?php

namespace App\Services\Gateways;

use App\DTO\Payment\CustomerDTO;
use App\DTO\Payment\PaymentDTO;
use App\DTO\Payment\Response\BoletoResponseDTO;
use App\DTO\Payment\Response\CreditCardResponseDTO;
use App\DTO\Payment\Response\GatewayCustomerResponseDTO;
use App\DTO\Payment\Response\PaymentResponseDTO;
use App\DTO\Payment\Response\PixResponseDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\GatewayException;
use App\Models\Payment;

class AsaasGateway extends BaseGateway
{
    public function processPayment(PaymentDTO $paymentDTO, Payment $payment): PaymentResponseDTO
    {
        $paymentData = $this->createPayment($paymentDTO, $payment->id);
        $paymentProcessData = match ($paymentDTO->method) {
            PaymentMethodEnum::PIX => $this->processPixPayment($paymentData),
            PaymentMethodEnum::BOLETO => $this->processBoletoPayment($paymentData),
            PaymentMethodEnum::CREDIT_CARD => $this->processCreditCardPayment($paymentData, $paymentDTO),
            default => throw new GatewayException($this->getName(), 'Método de pagamento não suportado')
        };
        $responseDTO = $this->getResponseDTO($paymentDTO->method, $paymentProcessData);
        return new PaymentResponseDTO(
            ...[...$responseDTO],
            externalPaymentId: $paymentData['id'],
            method: $paymentDTO->method->value,
            status: $this->mapStatus($paymentProcessData['status']),
            paidAt: $paymentProcessData['paidDate'] ?? null,
            gatewayResponse: $paymentProcessData,
            gatewayCustomerId: $paymentDTO->gatewayCustomer->id,
            apiClientCustomerId: $paymentDTO->apiClientCustomer?->id
        );
    }

    public function createCustomer(
        CustomerDTO $customerDTO,
        string|int|null $externalReference = null
    ): GatewayCustomerResponseDTO {
        $response = $this->makeRequest(
            'post',
            'customers',
            [
                'name' => $customerDTO->name,
                'cpfCnpj' => $customerDTO->cpf,
                'email' => $customerDTO->email,
                'phone' => $customerDTO->phone,
                'externalReference' => $externalReference,
            ]
        );
        $responseData = $response->json();
        return new GatewayCustomerResponseDTO(
            externalId: $responseData['id'],
            externalData: $responseData,
        );
    }

    private function createPayment(PaymentDTO $paymentDTO, ?string $externalReference = null): array
    {
        $payload = [
            'customer' => $paymentDTO->gatewayCustomer->external_id,
            'billingType' => $paymentDTO->method->value,
            'value' => $paymentDTO->amount,
            'dueDate' => now()->addDays(3)->format('Y-m-d'),
            'externalReference' => $externalReference,
        ];

        $response = $this->makeRequest(method: 'post', endpoint: 'payments', data: $payload);
        return $response->json();
    }

    private function processPixPayment(array $paymentData): array
    {
        $response = $this->makeRequest(
            method: 'post',
            endpoint: "payments/{$paymentData['id']}/pixQrCode"
        )->json();

        return $response->json();
    }

    private function processBoletoPayment(array $paymentData): array
    {
        return $paymentData;
    }

    private function processCreditCardPayment(array $paymentData, PaymentDTO $paymentDTO): array
    {
        $payload =  ['creditCardToken' => $this->tokenizeCard($paymentDTO)];
        $response = $this->makeRequest(
            method: 'post',
            endpoint: "payments/{$paymentData['id']}/payWithCreditCard",
            data: $payload
        );

        return $response->json();
    }

    private function tokenizeCard(PaymentDTO $paymentDTO): string
    {
        $creditCard = [
            'holderName' => $paymentDTO->creditCard->holderName,
            'number' => $paymentDTO->creditCard->number,
            'expiryMonth' => $paymentDTO->creditCard->expiryMonth,
            'expiryYear' => $paymentDTO->creditCard->expiryYear,
            'ccv' => $paymentDTO->creditCard->cvv,
        ];
        $customer = [
            'name' => $paymentDTO->creditCard->name,
            'email' => $paymentDTO->creditCard->email,
            'cpfCnpj' => $paymentDTO->creditCard->cpf,
            'postalCode' => $paymentDTO->creditCard->postalCode,
            'addressNumber' => $paymentDTO->creditCard->addressNumber,
            'phone' => $paymentDTO->creditCard->phone,
        ];
        $payload = [
            'customer' => $paymentDTO->gatewayCustomer->external_id,
            'creditCard' => $creditCard,
            'creditCardHolderInfo' => $customer
        ];

        $response = $this->makeRequest(
            method: 'post',
            endpoint: "creditCard/tokenizeCreditCard",
            data: $payload
        );
        $responseData = $response->json();
        return $responseData['creditCardToken'];
    }

    private function getResponseDTO(PaymentMethodEnum $method, array $paymentData): array
    {
        return match ($method) {
            PaymentMethodEnum::PIX => [
                'pixData' => new PixResponseDTO(
                    encodedImage: $paymentData['encodedImage'],
                    payload: $paymentData['payload'],
                    expiresAt: $paymentData['expirationDate'],
                )
            ],
            PaymentMethodEnum::BOLETO => [
                'boletoData' => new BoletoResponseDTO(
                    url: $paymentData['bankSlipUrl'],
                    dueDate: $paymentData['dueDate'],
                )
            ],

            PaymentMethodEnum::CREDIT_CARD => [
                'creditCardData' => new CreditCardResponseDTO(
                    lastFour: $paymentData['creditCard']['creditCardNumber'],
                    brand: $paymentData['creditCard']['creditCardBrand'] ?? null,
                    token: $paymentData['creditCard']['creditCardToken'],
                )
            ],
            default => []
        };
    }

    public function supportsMethod(string $method): bool
    {
        return in_array($method, [
            PaymentMethodEnum::CREDIT_CARD->value,
            PaymentMethodEnum::PIX->value,
            PaymentMethodEnum::BOLETO->value,
        ]);
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'CONFIRMED', 'RECEIVED' => PaymentStatusEnum::PAID->value,
            'AUTHORIZED' => PaymentStatusEnum::AUTHORIZED->value,
            'OVERDUE' => PaymentStatusEnum::OVERDUE->value,
            'REFUNDED' => PaymentStatusEnum::REFUNDED->value,
            default => PaymentStatusEnum::PENDING->value,
        };
    }

    protected function getDefaultHeaders(): array
    {
        return array_merge(parent::getDefaultHeaders(), [
            'access_token' => $this->configDTO->apiKey,
        ]);
    }
}
