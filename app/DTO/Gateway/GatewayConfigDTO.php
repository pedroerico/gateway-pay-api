<?php

namespace App\DTO\Gateway;

use App\Models\Gateway;

class GatewayConfigDTO
{
    public function __construct(
        public string $id,
        public string $apiKey,
        public string $baseUrl,
        public string $name,
        public ?array $config,
    ) {
    }
    public static function makeFromModel(Gateway $gateway): self
    {
        return new self(
            id: $gateway->id,
            apiKey: $gateway->api_key,
            baseUrl: $gateway->base_url,
            name: $gateway->name,
            config: $gateway->config?->toArray(),
        );
    }
}
