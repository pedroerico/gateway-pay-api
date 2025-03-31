<?php

namespace App\Services\Gateways;

use App\Enums\PaymentGatewayEnum;
use App\Enums\PaymentMethodEnum;
use InvalidArgumentException;

readonly class GatewayFactory
{
    public function __construct(
        private string $defaultGateway,
        private ?string $fallbackGateway = null
    ) {
    }

    public function create(string $gatewayName): PaymentGatewayInterface
    {
        $gateway = match ($gatewayName) {
            PaymentGatewayEnum::ASAAS->value => app(AsaasGateway::class),
            default => $this->getFallbackOrFail($gatewayName),
        };

        if (!$gateway instanceof PaymentGatewayInterface) {
            throw new InvalidArgumentException("Gateway inválido: {$gatewayName}");
        }

        return $gateway;
    }

    public function createForMethod(PaymentMethodEnum $method, ?string $preferredGateway = null): PaymentGatewayInterface
    {
        $gatewayName = $preferredGateway ?? $this->defaultGateway;
        $gateway = $this->create($gatewayName);

        if (!$gateway->supportsMethod($method->value)) {
            throw new InvalidArgumentException("O gateway {$gatewayName} não suporta o método {$method->value}");
        }

        return $gateway;
    }

    private function getFallbackOrFail(string $failedGateway): PaymentGatewayInterface
    {
        if ($this->fallbackGateway && $this->fallbackGateway !== $failedGateway) {
            return $this->create($this->fallbackGateway);
        }

        throw new InvalidArgumentException("Nenhum gateway disponível. Falha em: {$failedGateway}");
    }
}
