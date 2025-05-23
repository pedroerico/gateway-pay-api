<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumsToArrayTrait;

enum PaymentStatusEnum: string
{
    use EnumsToArrayTrait;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case AUTHORIZED = 'authorized';
    case OVERDUE = 'overdue';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::PROCESSING => 'processando',
            self::AUTHORIZED => 'Autorizado',
            self::OVERDUE => 'atrasado',
            self::PAID => 'Pago',
            self::FAILED => 'Falhou',
            self::REFUNDED => 'Reembolsado',
        };
    }

    public static function fromAsaasGateway(string $status): self
    {
        $statusEnum = PaymentGatewayStatusEnum::tryFrom(strtoupper($status));
        return match($statusEnum) {
            PaymentGatewayStatusEnum::CONFIRMED, PaymentGatewayStatusEnum::RECEIVED => self::PAID,
            PaymentGatewayStatusEnum::AUTHORIZED => self::AUTHORIZED,
            PaymentGatewayStatusEnum::OVERDUE => self::OVERDUE,
            PaymentGatewayStatusEnum::REFUNDED => self::REFUNDED,
            default => self::PENDING,
        };
    }
}
