<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\AbstractInterfaceDTO;
use App\DTO\Payment\PaymentDTO;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function create(AbstractInterfaceDTO $dto): Payment;
    public function update(Payment $payment, AbstractInterfaceDTO $dto): bool;
    public function getAllPayments(): LengthAwarePaginator;
    public function findPaymentById(string $id): ?Payment;
    public function findByGatewayId(string $gatewayId, string $gateway): ?Payment;
    public function updateStatus(string $gatewayId, string $status): Payment;
}
