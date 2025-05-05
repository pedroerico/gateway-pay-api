<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Gateway\CreateGatewayDTO;
use App\DTO\Payment\Response\GatewayCustomerResponseDTO;
use App\Enums\PaymentGatewayEnum;
use App\Models\Customer;
use App\Models\Gateway;
use App\Models\GatewayCustomer;
use App\Repositories\GatewayCustomerRepositoryInterface;
use App\Repositories\GatewayRepositoryInterface;

class GatewayService
{
    public function __construct(
        private readonly GatewayRepositoryInterface $gatewayRepository,
        private readonly GatewayCustomerRepositoryInterface $gatewayCustomerRepository,
    ) {
    }

    public function createGateway(CreateGatewayDTO $dto): Gateway
    {
        return $this->gatewayRepository->create($dto->toArray());
    }

    public function createGatewayCustomer(GatewayCustomerResponseDTO $customerResponseDTO): GatewayCustomer
    {
        return $this->gatewayCustomerRepository->create($customerResponseDTO->toArray());
    }

    public function getGatewayById(string $id): ?Gateway
    {
        return $this->gatewayRepository->findByActiveId($id);
    }

    public function getGatewayCustomer(Gateway $gateway, Customer $customer): ?GatewayCustomer
    {
        return $this->gatewayCustomerRepository->findByGatewayAndCustomer($gateway, $customer);
    }
}
