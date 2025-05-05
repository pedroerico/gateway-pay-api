<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Gateway;
use App\Models\GatewayCustomer;

interface GatewayCustomerRepositoryInterface
{
    public function create(array $data): GatewayCustomer;
    public function findByGatewayAndCustomer(Gateway $gateway, Customer $customer): ?GatewayCustomer;
    public function findByExternalId(string $externalId, Gateway $gateway): ?GatewayCustomer;
}
