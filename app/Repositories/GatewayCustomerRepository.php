<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Gateway;
use App\Models\GatewayCustomer;

class GatewayCustomerRepository implements GatewayCustomerRepositoryInterface
{
    public function create(array $data): GatewayCustomer
    {
        return GatewayCustomer::create($data);
    }

    public function findByGatewayAndCustomer(Gateway $gateway, Customer $customer): ?GatewayCustomer
    {
        return GatewayCustomer::where([
            'gateway_id' => $gateway->id,
            'customer_id' => $customer->id,
        ])->first();
    }
    public function findByExternalId(string $externalId, Gateway $gateway): ?GatewayCustomer
    {
        return GatewayCustomer::where([
            'external_id' => $externalId,
            'gateway_id' => $gateway->id,
        ])->first();
    }
}
