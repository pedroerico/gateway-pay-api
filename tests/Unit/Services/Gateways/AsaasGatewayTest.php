<?php

namespace Services\Gateways;

use App\DTO\Payment\CreditCardDTO;
use App\DTO\Payment\CustomerDTO;
use App\DTO\Payment\PaymentDTO;
use App\DTO\Payment\Response\BoletoResponseDTO;
use App\DTO\Payment\Response\CreditCardResponseDTO;
use App\DTO\Payment\Response\PaymentResponseDTO;
use App\DTO\Payment\Response\PixResponseDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Services\Gateways\AsaasGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasGatewayTest extends TestCase
{
    private AsaasGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new AsaasGateway(
            'test_api_key',
            'https://sandbox.asaas.com/api/v3',
            'cus_000000000001'
        );
    }

    public function testProcessPixPayment()
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments' => Http::response([
                'id' => 'pay_123',
                'status' => 'PENDING',
                'value' => 100.50,
            ]),
            'sandbox.asaas.com/api/v3/payments/pay_123/pixQrCode' => Http::response([
                'encodedImage' => 'iVBORw0KG...',
                'payload' => '000201...',
                'expirationDate' => '2023-12-31 23:59:59'
            ])
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        $result = $this->gateway->processPayment($paymentDTO);

        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertInstanceOf(PixResponseDTO::class, $result->pixData);
        $this->assertEquals('pay_123', $result->externalPaymentId);
        $this->assertEquals('PENDING', $result->status);
    }

    public function testProcessBoletoPayment()
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments' => Http::response([
                'id' => 'pay_456',
                'status' => 'PENDING',
                'value' => 200.75,
                'bankSlipUrl' => 'https://sandbox.asaas.com/b/pdf/123',
                'dueDate' => '2023-12-31'
            ])
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 200.75,
            method: PaymentMethodEnum::BOLETO,
            status: PaymentStatusEnum::PENDING
        );

        $result = $this->gateway->processPayment($paymentDTO);

        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertInstanceOf(BoletoResponseDTO::class, $result->boletoData);
        $this->assertEquals('pay_456', $result->externalPaymentId);
        $this->assertEquals('PENDING', $result->status);
    }

    public function testProcessCreditCardPayment()
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments' => Http::response([
                'id' => 'pay_789',
                'status' => 'PENDING',
                'value' => 300.25
            ]),
            'sandbox.asaas.com/api/v3/payments/pay_789/payWithCreditCard' => Http::response([
                'status' => 'CONFIRMED',
                'creditCard' => [
                    'creditCardNumber' => '**** **** **** 4540',
                    'creditCardBrand' => 'VISA',
                    'creditCardToken' => 'tok_123'
                ]
            ])
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 300.25,
            method: PaymentMethodEnum::CREDIT_CARD,
            status: PaymentStatusEnum::PENDING,
            creditCard: new CreditCardDTO(
                holderName: 'John Doe',
                number: '4111111111111111',
                expiryMonth: '12',
                expiryYear: '2025',
                cvv: '123'
            ),
            customer: new CustomerDTO(
                name: 'John Doe',
                email: 'john@example.com',
                cpf: '12345678901',
                postalCode: '12345678',
                addressNumber: '123',
                phone: '11999999999'
            )
        );

        $result = $this->gateway->processPayment($paymentDTO);

        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertInstanceOf(CreditCardResponseDTO::class, $result->creditCardData);
        $this->assertEquals('pay_789', $result->externalPaymentId);
        $this->assertEquals('CONFIRMED', $result->status);
    }

    public function testThrowsExceptionOnFailedRequest()
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments' => Http::response([], 500)
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Asaas Gateway Error: 500');

        $this->gateway->processPayment($paymentDTO);
    }
}
