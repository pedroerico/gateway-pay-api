<?php

namespace App\Services;

use App\DTO\Payment\PaymentDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\PaymentFailed;
use App\Events\PaymentProcessed;
use App\Exceptions\PaymentException;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\PaymentProcessingException;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Repositories\PaymentRepositoryInterface;
use App\Services\Gateways\CircuitBreaker;
use App\Services\Gateways\GatewayFactory;
use App\Services\Gateways\PaymentGatewayInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private bool $useQueue;

    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected CircuitBreaker $circuitBreaker,
        protected GatewayFactory $gatewayFactory,
    ) {
        $this->useQueue = env('PAYMENT_USE_QUEUE', true);
    }

    public function create(PaymentDTO $paymentDTO): Payment
    {
        $payment = $this->paymentRepository->create($paymentDTO);
        if ($paymentDTO->method === PaymentMethodEnum::CREDIT_CARD || !$this->useQueue) {
            $gateway = $this->gatewayFactory->createForMethod($paymentDTO->method);
            $this->processPayment($payment, $paymentDTO, $gateway);
        } else {
            ProcessPayment::dispatch($payment, $paymentDTO)->onQueue('payments');
        }
        return $payment;
    }

    public function getPayments(): LengthAwarePaginator
    {
        return $this->paymentRepository->getAllPayments();
    }

    public function getPaymentById(string $id): ?Payment
    {
        $payment = $this->paymentRepository->findPaymentById($id);

        if (!$payment) {
            throw new PaymentException('Pagamento não encontrado');
        }

        return $payment;
    }

    public function processPayment(Payment $payment, PaymentDTO $paymentDTO, PaymentGatewayInterface $gateway): Payment
    {
        try {
            if (!$this->circuitBreaker->isAvailable()) {
                throw new \Exception('Payment gateway is temporarily unavailable');
            }
            $response = $gateway->processPayment($paymentDTO);

            $this->paymentRepository->update($payment, $response);

            $this->circuitBreaker->recordSuccess();
            event(new PaymentProcessed($payment, $response));
            return $payment;
        } catch (PaymentGatewayException $e) {
            $this->circuitBreaker->recordFailure();
            $this->paymentRepository->updateStatus($payment->id, PaymentStatusEnum::FAILED->value);

            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            event(new PaymentFailed($payment, $e->getMessage()));

            throw new PaymentProcessingException(
                'Failed to process payment: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
