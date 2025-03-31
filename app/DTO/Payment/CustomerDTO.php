<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;

class CustomerDTO extends AbstractDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $cpfCnpj,
        public string $postalCode,
        public string $addressNumber,
        public string $phone,
    ) {
    }

    public static function FromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            cpfCnpj: $data['cpf_cnpj'],
            postalCode: $data['postal_code'],
            addressNumber: $data['address_number'],
            phone: $data['phone']
        );
    }
}
