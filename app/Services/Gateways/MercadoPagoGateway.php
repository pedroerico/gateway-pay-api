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

class MercadoPagoGateway extends BaseGateway
{
    public function processPayment(PaymentDTO $paymentDTO, Payment $payment): PaymentResponseDTO
    {
        $paymentData = match ($paymentDTO->method) {
            PaymentMethodEnum::PIX => $this->processPixPayment($paymentDTO, $payment->id),
            PaymentMethodEnum::BOLETO => $this->processBoletoPayment($paymentDTO, $payment->id),
            PaymentMethodEnum::CREDIT_CARD => $this->processCreditCardPayment($paymentDTO, $payment->id),
            default => throw new GatewayException($this->getName(), 'Método de pagamento não suportado')
        };

        $responseDTO = $this->getResponseDTO($paymentDTO->method, $paymentData);
        return new PaymentResponseDTO(
            ...[...$responseDTO],
            externalPaymentId: $paymentData['id'],
            method: $paymentDTO->method->value,
            status: $this->mapStatus($paymentData['status']),
            paidAt: $paymentData['date_approved'] ?? null,
            gatewayResponse: $paymentData,
            gatewayCustomerId: $paymentDTO->gatewayCustomer->id,
            apiClientCustomerId: $paymentDTO->apiClientCustomer?->id,
        );
    }

    public function createCustomer(
        CustomerDTO $customerDTO,
        string|int|null $externalReference = null
    ): GatewayCustomerResponseDTO {

        $responseData = $this->getCustomer($customerDTO);
        if (!$responseData) {
            $payload = [
                'first_name' => $customerDTO->name,
                'identification' => [
                    'type' => 'cpf',
                    'number' => $customerDTO->cpf
                ],
                'email' => $customerDTO->email,
            ];
            $response = $this->makeRequest(
                method: 'post',
                endpoint: 'customers',
                data: $payload
            );

            $responseData = $response->json();
        }

        return new GatewayCustomerResponseDTO(
            externalId: $responseData['id'],
            externalData: $responseData,
        );
    }

    private function getCustomer(CustomerDTO $customerDTO): array
    {
        $response = $this->makeRequest(
            method: 'get',
            endpoint: 'customers/search',
            data: ['email' => $customerDTO->email]
        );

        $responseData = $response->json();
        return $responseData['results'][0] ?? [];
    }

    private function processPixPayment(PaymentDTO $paymentDTO, string $externalReference): array
    {
        $payload = [
            'transaction_amount' => $paymentDTO->amount,
            'payment_method_id' => PaymentMethodEnum::PIX,
            'payer' => [
                'email' => $paymentDTO->customer->email,
                'first_name' => $paymentDTO->customer->name,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $paymentDTO->customer->cpf
                ]
            ]
        ];

        $response = $this->makeRequest(
            method: 'post',
            endpoint: 'payments',
            data: $payload,
            customHeaders: ['X-Idempotency-Key' => $externalReference]
        );

        return $response->json();
    }

    private function processBoletoPayment(PaymentDTO $paymentDTO, ?string $externalReference = null): array
    {
        $payload = [
            'transaction_amount' => $paymentDTO->amount,
            'payment_method_id' => "bolbradesco",
            'payer' => [
                'email' => $paymentDTO->customer->email,
                'first_name' => $paymentDTO->customer->name,
                'last_name' => 'dd',
                'identification' => [
                    'type' => 'CPF',
                    'number' => $paymentDTO->customer->cpf
                ],
            ]
        ];
        $response = $this->makeRequest(
            method: 'post',
            endpoint: 'payments',
            data: $payload,
            customHeaders: ['X-Idempotency-Key' => $externalReference]
        );

        return $response->json();
    }

    private function processCreditCardPayment(PaymentDTO $paymentDTO, ?string $externalReference = null): array
    {
        $token = $this->tokenizeCard($paymentDTO);
        $cardBrand = $this->detectCardBrand($paymentDTO->creditCard->number);
        $payload = [
            'transaction_amount' => $paymentDTO->amount,
            'payment_method_id' => $cardBrand,
            'installments' => 1,
            'token' => $token,
            'payer' => [
                'email' => $paymentDTO->customer->email,
                'first_name' => $paymentDTO->customer->name,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $paymentDTO->creditCard->cpf
                ]
            ]
        ];

        $response = $this->makeRequest(
            method: 'post',
            endpoint: 'payments',
            data: $payload,
            customHeaders: ['X-Idempotency-Key' => $externalReference]
        );

        return [...$response->json(), 'token' => $token, 'card_brand' => $cardBrand];
    }

    private function tokenizeCard(PaymentDTO $paymentDTO): string
    {
        $payload = [
            'card_number' => $paymentDTO->creditCard->number,
            'expiration_month' => $paymentDTO->creditCard->expiryMonth,
            'expiration_year' => $paymentDTO->creditCard->expiryYear,
            'security_code' => $paymentDTO->creditCard->cvv,
            'cardholder' => [
                'name' => $paymentDTO->creditCard->name,
                'identification' => [
                    'type' => 'CPF',
                    'number' => $paymentDTO->creditCard->cpf
                ]
            ],
        ];

        $response = $this->makeRequest(
            method: 'post',
            endpoint: "card_tokens",
            data: $payload
        );
        $responseData = $response->json();
        return $responseData['id'];
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
            'approved' => PaymentStatusEnum::PAID->value,
            'pending' => PaymentStatusEnum::PENDING->value,
            'authorized' => PaymentStatusEnum::AUTHORIZED->value,
            'in_process' => PaymentStatusEnum::PROCESSING->value,
            'rejected' => PaymentStatusEnum::FAILED->value,
            'refunded' => PaymentStatusEnum::REFUNDED->value,
            default => PaymentStatusEnum::PENDING->value,
        };
    }

    private function getResponseDTO(PaymentMethodEnum $method, array $paymentData): array
    {
        return match ($method) {
            PaymentMethodEnum::PIX => [
                'pixData' => new PixResponseDTO(
                    encodedImage: 'data:image/png;base64,' . (
                        $paymentData['point_of_interaction']['transaction_data']['qr_code_base64'] ?? ''
                    ),
                    payload: $paymentData['point_of_interaction']['transaction_data']['qr_code'] ?? '',
                    url: $paymentData['point_of_interaction']['transaction_data']['ticket_url'] ?? '',
                    expiresAt: $paymentData['point_of_interaction']['transaction_data']['qr_code_expiration_date']
                    ?? now()->addMinutes(30)->toISOString(),
                )
            ],
            PaymentMethodEnum::BOLETO => [
                'boletoData' => new BoletoResponseDTO(
                    url: $paymentData['transaction_details']['external_resource_url'] ?? '',
                    dueDate: $paymentData['date_of_expiration'] ?? null,
                    barcode: $paymentData['transaction_details']['barcode']['content'] ?? '',
                )
            ],
            PaymentMethodEnum::CREDIT_CARD => [
                'creditCardData' => new CreditCardResponseDTO(
                    lastFour: $paymentData['card']['last_four_digits'] ?? '',
                    brand: $paymentData['card_brand'] ?? '',
                    token: $paymentData['token'] ?? null,
                )
            ],
            default => []
        };
    }

    protected function getDefaultHeaders(): array
    {
        return array_merge(parent::getDefaultHeaders(), [
            'Authorization' => 'Bearer ' . $this->configDTO->apiKey,
        ]);
    }
}
