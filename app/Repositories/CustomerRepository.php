<?php

namespace App\Repositories;

use App\DTO\Payment\CustomerDTO;
use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function firstOrCreate(CustomerDTO $customerDTO): Customer
    {
        return Customer::firstOrCreate(
            ['cpf' => $customerDTO->cpf],
            [
                'name' => $customerDTO->name,
                'email' => $customerDTO->email,
            ]
        );
    }
}
