<?php

namespace App\Jobs;

use App\DTO\Payment\PaymentDTO;
use App\Services\Gateways\GatewayFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public PaymentDTO $paymentDTO,
        public int $tries = 3,
        public int $maxExceptions = 2,
        public int $timeout = 60,
    ) {
    }

    public function handle(GatewayFactory $gatewayFactory): void
    {
        $gateway = $gatewayFactory->createForMethod($this->paymentDTO->method);

        $processor = app(PaymentService::class);
        $processor->processPayment($this->payment, $this->paymentDTO, $gateway);
    }

    public function failed(\Throwable $exception): void
    {
        $this->payment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'gateway_response' => [
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
        ]);
    }
}
