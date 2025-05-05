<?php

declare(strict_types=1);

namespace App\DTO\Payment\Response;

use App\DTO\AbstractDTO;
use App\Models\Customer;
use App\Models\Gateway;

class GatewayCustomerResponseDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $externalId,
        public readonly ?array $externalData = null,
        protected ?string $gatewayId = null,
        protected ?string $customerId = null,
    ) {
    }

    public function setGatewayAndCustomer(Gateway $gateway, Customer $customer): void
    {
        $this->gatewayId = $gateway->id;
        $this->customerId = $customer->id;
    }
}
