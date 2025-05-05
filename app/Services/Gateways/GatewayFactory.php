<?php

namespace App\Services\Gateways;

use App\DTO\Gateway\GatewayConfigDTO;
use App\Enums\PaymentGatewayEnum;
use App\Enums\PaymentMethodEnum;
use App\Exceptions\PaymentException;
use App\Models\Gateway;
use App\Repositories\GatewayRepositoryInterface;
use InvalidArgumentException;

readonly class GatewayFactory
{
    public function __construct(
        protected GatewayRepositoryInterface $gatewayRepository,
    ) {
    }

    public function create(Gateway $gateway, bool $enableFallback = true): PaymentGatewayInterface
    {
        $configDTO = GatewayConfigDTO::makeFromModel($gateway);
        $gatewayClass = match ($gateway->code) {
            PaymentGatewayEnum::ASAAS->value => new AsaasGateway($configDTO),
            PaymentGatewayEnum::MERCADO_PAGO->value => new MercadoPagoGateway($configDTO),
            default => throw new \InvalidArgumentException("Gateway não implementado"),
        };

        if (!$gatewayClass instanceof PaymentGatewayInterface) {
            throw new InvalidArgumentException("Gateway inválido: {$gateway->name}");
        }

        return $gatewayClass;
    }

    public function createForMethod(
        PaymentMethodEnum $method,
        ?string $preferredGateway = null
    ): PaymentGatewayInterface {
        $gateway = $this->gatewayRepository->getByPriority($preferredGateway);
        if (!$gateway) {
            throw new PaymentException('Nenhum gateway encontrado');
        }
        $gatewayClass = $this->create($gateway);

        if (!$gatewayClass->supportsMethod($method->value)) {
            throw new InvalidArgumentException("O gateway {$gatewayClass->getName()} não suporta o método {$method->value}");
        }

        return $gatewayClass;
    }
}
