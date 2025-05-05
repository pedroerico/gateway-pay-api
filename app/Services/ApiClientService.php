<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ApiClient\CreateApiClientDTO;
use App\Models\ApiClient;
use App\Models\ApiClientCustomer;
use App\Models\Customer;
use App\Repositories\ApiClientCustomerRepositoryInterface;
use App\Repositories\ApiClientRepositoryInterface;

class ApiClientService
{
    public function __construct(
        private readonly ApiClientRepositoryInterface $apiClientRepository,
        private readonly ApiClientCustomerRepositoryInterface $apiClientCustomerRepository
    ) {
    }

    public function create(CreateApiClientDTO $dto): ApiClient
    {
        if ($this->apiClientRepository->findByApiKey($dto->apiKey)) {
            throw new \Exception("Cliente API com essa chave já existe");
        }

        return $this->apiClientRepository->create($dto->toArray());
    }

    public function getCustomerByExternalId(ApiClient $apiClient, string $externalId): ?ApiClientCustomer
    {
        return $this->apiClientCustomerRepository->findByExternalId($apiClient, $externalId);
    }

    public function firstOrCreateByCustomer(
        ApiClient $apiClient,
        Customer $customer,
        ?string $externalId = null
    ): ?ApiClientCustomer {
        return $this->apiClientCustomerRepository->firstOrCreateByCustomer($apiClient, $customer->id, $externalId);
    }
}
