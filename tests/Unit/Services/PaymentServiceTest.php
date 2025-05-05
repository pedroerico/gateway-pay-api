<?php

namespace Tests\Unit\Services;

use App\DTO\Payment\PaymentDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\PaymentProcessed;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Repositories\PaymentRepositoryInterface;
use App\Services\Gateways\CircuitBreaker;
use App\Services\Gateways\PaymentGatewayInterface;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private $paymentRepositoryMock;
    private $gatewayMock;
    private $circuitBreakerMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar mocks para todas as dependências
        $this->paymentRepositoryMock = Mockery::mock(PaymentRepositoryInterface::class);
        $this->gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
        $this->circuitBreakerMock = Mockery::mock(CircuitBreaker::class);

        // Instanciar o service com os mocks
        $this->paymentService = new PaymentService(
            $this->paymentRepositoryMock,
            $this->gatewayMock,
            $this->circuitBreakerMock
        );

        Queue::fake();
        Event::fake();
    }

    public function test_create_payment_successfully()
    {
        // 1. Preparar DTO de entrada
        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        // 2. Mock do repository
        $expectedPayment = new Payment([
            'amount' => 100.50,
            'method' => PaymentMethodEnum::PIX->value,
            'status' => PaymentStatusEnum::PENDING->value
        ]);

        $this->paymentRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::type(PaymentDTO::class))
            ->andReturn($expectedPayment);

        // 3. Executar
        $result = $this->paymentService->create($paymentDTO);

        // 4. Verificações
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals(100.50, $result->amount);

        Queue::assertPushed(ProcessPayment::class, function ($job) use ($expectedPayment, $paymentDTO) {
            return $job->payment->id === $expectedPayment->id &&
                $job->paymentDTO === $paymentDTO;
        });
    }

    public function test_process_payment_successfully()
    {
        // 1. Preparar dados
        $payment = Payment::factory()->create([
            'status' => PaymentStatusEnum::PENDING->value
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::CREDIT_CARD,
            status: PaymentStatusEnum::PENDING
        );

        // 2. Configurar mocks
        $this->circuitBreakerMock
            ->shouldReceive('isAvailable')
            ->once()
            ->andReturn(true);

        $this->gatewayMock
            ->shouldReceive('processPayment')
            ->once()
            ->with($paymentDTO)
            ->andReturn(new \App\DTO\Payment\Response\PaymentResponseDTO(
                externalPaymentId: 'pay_123',
                gateway: 'asaas',
                method: 'credit_card',
                status: 'paid',
                paidAt: now(),
                clientId: 'cus_123',
                gatewayResponse: [],
                creditCardData: new \App\DTO\Payment\Response\CreditCardResponseDTO(
                    lastFour: '1111',
                    brand: 'visa',
                    token: 'tok_123'
                )
            ));

        $this->circuitBreakerMock
            ->shouldReceive('recordSuccess')
            ->once();

        $this->paymentRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($payment, Mockery::type(\App\DTO\Payment\Response\PaymentResponseDTO::class))
            ->andReturn(true);

        // 3. Executar
        $this->paymentService->processPayment($payment, $paymentDTO);

        // 4. Verificações
        Event::assertDispatched(PaymentProcessed::class);
    }

    public function test_process_payment_fails_when_circuit_breaker_open()
    {
        $payment = Payment::factory()->create();
        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        $this->circuitBreakerMock
            ->shouldReceive('isAvailable')
            ->once()
            ->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment gateway is temporarily unavailable');

        $this->paymentService->processPayment($payment, $paymentDTO);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
