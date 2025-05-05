<?php

declare(strict_types=1);

namespace App\DTO\ApiClient;

use App\DTO\AbstractDTO;
use Illuminate\Support\Str;

class CreateApiClientDTO extends AbstractDTO
{
    public function __construct(
        public string $name,
        public ?array $allowedIps = null,
        public ?bool $isActive = true,
        public ?string $apiKey = null,
        public ?string $apiSecret = null
    ) {
        $this->apiKey ??= Str::uuid()->toString();
        $this->apiSecret ??= Str::random(64);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            allowedIps: $data['allowed_ips'] ?? null,
            isActive: $data['is_active'] ?? true
        );
    }
}
