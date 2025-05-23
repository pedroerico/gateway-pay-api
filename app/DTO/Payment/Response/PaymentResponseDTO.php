<?php

declare(strict_types=1);

namespace App\DTO\Payment\Response;

use App\DTO\AbstractDTO;
use App\Enums\PaymentStatusEnum;

class PaymentResponseDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $gatewayId,
        public readonly string $method,
        public readonly string $status,
        public readonly string $gateway,
        public readonly ?string $clientId,
        public readonly ?PixResponseDTO $pixData = null,
        public readonly ?BoletoResponseDTO $boletoData = null,
        public readonly ?CreditCardResponseDTO $creditCardData = null,
        public ?\DateTime $paidAt = null,
        public readonly ?array $gatewayResponse = null
    ) {
        if ($status === PaymentStatusEnum::PAID->value && $paidAt === null) {
            $this->paidAt = now();
        }
    }
}
