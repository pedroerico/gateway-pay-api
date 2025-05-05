<?php

declare(strict_types=1);

namespace App\DTO\Payment\Response;

use App\DTO\AbstractDTO;

class PixResponseDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $encodedImage,
        public readonly string $payload,
        public readonly ?string $url = null,
        public readonly ?string $expiresAt = null,
    ) {
    }
}
