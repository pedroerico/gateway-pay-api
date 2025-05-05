<?php

namespace Tests\Integration;

use App\DTO\Payment\PaymentDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Services\Gateways\AsaasGateway;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function testPaymentIsProcessedViaQueue()
    {
        Queue::fake();

        $payment = Payment::factory()->create([
            'amount' => 100.50,
            'method' => 'pix',
            'status' => 'pending'
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        ProcessPayment::dispatch($payment, $paymentDTO);

        Queue::assertPushed(ProcessPayment::class, function ($job) use ($payment) {
            return $job->payment->id === $payment->id;
        });
    }

    public function testPaymentServiceProcessesPaymentCorrectly()
    {
        $gatewayMock = Mockery::mock(AsaasGateway::class);
        $gatewayMock->shouldReceive('processPayment')
            ->once()
            ->andReturn(new \App\DTO\Payment\Response\PaymentResponseDTO(
                externalPaymentId: 'pay_123',
                method: 'pix',
                status: 'paid',
                gateway: 'asaas',
                clientId: 'cus_123',
                pixData: new \App\DTO\Payment\Response\PixResponseDTO(
                    encodedImage: 'iVBORw0KG...',
                    payload: '000201...',
                    expiresAt: now()->addDay()
                ),
                paidAt: now(),
                gatewayResponse: []
            ));

        $payment = Payment::factory()->create([
            'amount' => 100.50,
            'method' => 'pix',
            'status' => 'pending'
        ]);

        $paymentDTO = new PaymentDTO(
            amount: 100.50,
            method: PaymentMethodEnum::PIX,
            status: PaymentStatusEnum::PENDING
        );

        $service = new PaymentService(
            app(\App\Repositories\PaymentRepositoryInterface::class),
            $gatewayMock,
            app(\App\Services\Gateways\CircuitBreaker::class),
            app(\App\Services\MetricsService::class)
        );

        $service->processPayment($payment, $paymentDTO);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid'
        ]);
    }
}
