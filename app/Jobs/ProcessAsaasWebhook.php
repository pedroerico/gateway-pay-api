<?php

namespace App\Jobs;

use App\Enums\PaymentGatewayEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentGatewayStatusEnum;
use App\Models\Payment;
use App\Repositories\PaymentRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAsaasWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function handle(PaymentRepositoryInterface $paymentRepository)
    {
        $payment = $paymentRepository->findByGatewayId(
            $this->payload['payment']['id'],
            PaymentGatewayEnum::ASAAS->value
        );

        if (!$payment) {
            Log::warning('Asaas Webhook: Pagamento não encontrado', $this->payload);
            return;
        }
        if ($this->payload['event'] === PaymentGatewayStatusEnum::PAYMENT_DELETED->value) {
            $payment->delete();
        } else {
            $this->updateStatus($payment, PaymentStatusEnum::fromAsaasGateway($this->payload['payment']['status']));
        }
    }

    protected function updateStatus(Payment $payment, PaymentStatusEnum $status): void
    {
        $payment->update(['status' => $status->value, 'paid_at' => now()]);
    }
}
