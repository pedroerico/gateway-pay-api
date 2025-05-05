<?php

namespace App\Repositories;

use App\Models\ApiClient;
use App\Models\ApiClientCustomer;

class ApiClientCustomerRepository implements ApiClientCustomerRepositoryInterface
{
    public function create(array $data): ApiClientCustomer
    {
        return ApiClientCustomer::create($data);
    }

    public function findByExternalId(ApiClient $apiClient, string $externalId): ?ApiClientCustomer
    {
        return ApiClientCustomer::where([
            'external_id' => $externalId,
            'api_client_id' => $apiClient->id
        ])->first();
    }

    public function firstOrCreateByCustomer(
        ApiClient $apiClient,
        string $customerId,
        ?string $externalId = null
    ): ?ApiClientCustomer {
        return ApiClientCustomer::firstOrCreate(
            [
                'customer_id' => $customerId,
                'api_client_id' => $apiClient->id
            ],
            [
                'external_id' => $externalId
            ]
        )->first();
    }
}
