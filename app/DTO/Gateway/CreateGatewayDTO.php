<?php

declare(strict_types=1);

namespace App\DTO\Gateway;

use App\DTO\AbstractDTO;

class CreateGatewayDTO extends AbstractDTO
{
    public function __construct(
        public string $name,
        public string $code,
        public string $baseUrl,
        public string $apiKey,
        public string|int|null $priority = null,
        public bool $isActive = true,
        public ?string $webhookHeader = null,
        public ?string $webhookToken = null,
        public ?array $config = null
    ) {
        $this->webhookToken ??= bin2hex(random_bytes(16));
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'],
            baseUrl: $data['base_url'],
            apiKey: $data['api_key'],
            priority: (int) $data['priority'] ?? null,
            isActive: $data['is_active'] ?? true,
            webhookHeader: $data['webhook_header'] ?? 'x-webhook-token',
            config: $data['config'] ?? null
        );
    }
}
