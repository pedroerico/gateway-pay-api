<?php

namespace App\Observers;

use App\Enums\PaymentGatewayEnum;
use App\Models\Gateway;

class GatewayObserver
{
    public function creating(Gateway $gateway): void
    {
        if (!in_array($gateway->code, PaymentGatewayEnum::toArray())) {
            throw new \Exception("O código do gateway é inválido.");
        }

        if (empty($gateway->priority)) {
            $gateway->priority = $this->getNextPriority();
        } else {
            $this->shiftPriorities($gateway->priority);
        }
    }

    protected function getNextPriority(): int
    {
        return (Gateway::max('priority') ?? 0) + 1;
    }

    protected function shiftPriorities(int $fromPriority): void
    {
        Gateway::where('priority', '>=', $fromPriority)
            ->orderBy('priority', 'desc')
            ->increment('priority');
    }
}
