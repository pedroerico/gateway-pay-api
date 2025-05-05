<?php

namespace App\Repositories;

use App\DTO\Payment\CustomerDTO;
use App\Models\Customer;

interface CustomerRepositoryInterface
{
    public function firstOrCreate(CustomerDTO $customerDTO): Customer;
}
