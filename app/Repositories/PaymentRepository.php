<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\AbstractInterfaceDTO;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(AbstractInterfaceDTO $dto): Payment
    {
        return Payment::create($dto->toArray());
    }

    public function update(Payment $payment, AbstractInterfaceDTO $dto): bool
    {
        return $payment->update($dto->toArray());
    }

    public function getAllPayments(): LengthAwarePaginator
    {
        return Payment::query()->latest()->paginate();
    }

    public function findPaymentById(string $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findByGatewayId(string $gatewayId, string $gateway): ?Payment
    {
        return Payment::where('gateway_id', $gatewayId)
            ->where('gateway', $gateway)
            ->first();
    }

    public function updateStatus(string $gatewayId, string $status): Payment
    {
        return Payment::where('gateway_id', $gatewayId)->update(['status' => $status]);
    }
}
