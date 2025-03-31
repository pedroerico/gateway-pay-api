<?php

declare(strict_types=1);

namespace App\DTO\Payment\Response;

use App\DTO\AbstractDTO;

class CreditCardResponseDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $lastFour,
        public readonly string $brand,
        public readonly string $token
    ) {
    }
}
