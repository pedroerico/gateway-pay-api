<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Payment\CustomerDTO;
use App\Models\Customer;
use App\Repositories\CustomerRepositoryInterface;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    public function create(CustomerDTO $dto): Customer
    {
        return $this->customerRepository->firstOrCreate($dto);
    }
}
