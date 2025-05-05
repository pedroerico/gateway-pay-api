<?php

declare(strict_types=1);

namespace App\DTO\Payment\Response;

use App\DTO\AbstractDTO;

class BoletoResponseDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $url,
        public readonly ?string $dueDate = null,
        public readonly ?string $barcode = null
    ) {
    }
}
