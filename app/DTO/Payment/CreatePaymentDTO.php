<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;

class CreatePaymentDTO extends AbstractDTO
{
    public function __construct(
        public float $amount,
        public string $method,
        public string $status,
        public ?int $apiClientCustomerId = null,
        public ?array $metadata = null,
    ) {
    }
}
