<?php

namespace App\Events;

use App\DTO\Payment\Response\PaymentResponseDTO;
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Payment $payment, public PaymentResponseDTO $paymentResponseDTO)
    {
    }
}
