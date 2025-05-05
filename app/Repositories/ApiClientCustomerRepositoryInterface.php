<?php

namespace App\Repositories;

use App\Models\ApiClient;
use App\Models\ApiClientCustomer;

interface ApiClientCustomerRepositoryInterface
{
    public function create(array $data): ApiClientCustomer;
    public function findByExternalId(ApiClient $apiClient, string $externalId): ?ApiClientCustomer;
    public function firstOrCreateByCustomer(
        ApiClient $apiClient,
        string $customerId,
        ?string $externalId = null
    ): ?ApiClientCustomer;
}
