<?php

namespace App\Services\Gateways;

use App\DTO\Payment\PaymentDTO;
use App\DTO\Payment\Response\BoletoResponseDTO;
use App\DTO\Payment\Response\CreditCardResponseDTO;
use App\DTO\Payment\Response\PaymentResponseDTO;
use App\DTO\Payment\Response\PixResponseDTO;
use App\Enums\PaymentGatewayEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\GatewayException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements PaymentGatewayInterface
{
    private string $status;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $clientId
    ) {
    }

    public function processPayment(PaymentDTO $paymentDTO): PaymentResponseDTO
    {
        $paymentData = $this->createPayment($paymentDTO);
        $this->setStatus($paymentData['status']);
        $specificParams = match ($paymentDTO->method) {
            PaymentMethodEnum::PIX => ['pixData' => $this->getPixData($paymentData)],
            PaymentMethodEnum::BOLETO => ['boletoData' => $this->getBoletoData($paymentData)],
            PaymentMethodEnum::CREDIT_CARD => ['creditCardData' => $this->getCreditCardData($paymentData, $paymentDTO)],
            default => throw new GatewayException($this->getName(), 'Método de pagamento não suportado')
        };
        $baseParams = [
            'gatewayId' => $paymentData['id'],
            'gateway' => $this->getName(),
            'method' => $paymentDTO->method->value,
            'status' => PaymentStatusEnum::fromAsaasGateway($this->getStatus())->value,
            'paidAt' => $paymentData['paidDate'] ?? null,
            'clientId' => $this->clientId,
            'gatewayResponse' => $paymentData
        ];
        return new PaymentResponseDTO(...[...$baseParams, ...$specificParams]);
    }

    private function createPayment(PaymentDTO $paymentDTO): array
    {
        $payload = [
            'customer' => $this->clientId,
            'billingType' => $paymentDTO->method->value,
            'value' => $paymentDTO->amount,
            'dueDate' => now()->addDays(3)->format('Y-m-d'),
        ];

        $response = $this->makeRequest('post', 'payments', $payload);
        return $response->json();
    }

    private function getPixData(array $paymentData): PixResponseDTO
    {
        $pixResponse = $this->makeRequest(
            'post',
            "payments/{$paymentData['id']}/pixQrCode"
        )->json();

        return new PixResponseDTO(
            encodedImage: $pixResponse['encodedImage'],
            payload: $pixResponse['payload'],
            expiresAt: $pixResponse['expirationDate'],
        );
    }

    private function getBoletoData(array $paymentData): BoletoResponseDTO
    {
        return new BoletoResponseDTO(
            url: $paymentData['bankSlipUrl'],
            dueDate: $paymentData['dueDate'],
        );
    }

    private function getCreditCardData(array $paymentData, PaymentDTO $paymentDTO): CreditCardResponseDTO
    {
        $creditCard = [
            'holderName' => $paymentDTO->creditCard->holderName,
            'number' => $paymentDTO->creditCard->number,
            'expiryMonth' => $paymentDTO->creditCard->expiryMonth,
            'expiryYear' => $paymentDTO->creditCard->expiryYear,
            'ccv' => $paymentDTO->creditCard->cvv,
        ];

        $customer = [
            'name' => $paymentDTO->customer->name,
            'email' => $paymentDTO->customer->email,
            'cpfCnpj' => $paymentDTO->customer->cpfCnpj,
            'postalCode' => $paymentDTO->customer->postalCode,
            'addressNumber' => $paymentDTO->customer->addressNumber,
            'phone' => $paymentDTO->customer->phone,
        ];
        $payload = ['creditCard' => $creditCard, 'creditCardHolderInfo' => $customer];

        $response = $this->makeRequest(
            'post',
            "payments/{$paymentData['id']}/payWithCreditCard",
            $payload
        );
        $responseData = $response->json();
        $this->setStatus($responseData['status']);
        return new CreditCardResponseDTO(
            lastFour: $responseData['creditCard']['creditCardNumber'],
            brand: $responseData['creditCard']['creditCardBrand'] ?? null,
            token: $responseData['creditCard']['creditCardToken'],
        );
    }

    public function getName(): string
    {
        return PaymentGatewayEnum::ASAAS->value;
    }

    public function supportsMethod(string $method): bool
    {
        return in_array($method, [
            PaymentMethodEnum::CREDIT_CARD->value,
            PaymentMethodEnum::PIX->value,
            PaymentMethodEnum::BOLETO->value,
        ]);
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->$method($url, $data);

        if ($response->failed()) {
            Log::error('Asaas Gateway Error', [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            if (str_starts_with((string)$response->status(), '4')) {
                throw new GatewayException(
                    gatewayName: $this->getName(),
                    message: 'Erro ao processar a requisição no Asaas',
                    code: $response->status(),
                    errors: $response->json()['errors'] ?? []
                );
            }
            throw new \Exception("Asaas Gateway Error: {$response->status()}");
        }

        return $response;
    }

    private function getStatus(): string
    {
        return $this->status;
    }

    private function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
