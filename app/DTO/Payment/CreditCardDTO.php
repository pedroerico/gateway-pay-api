<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;

class CreditCardDTO extends AbstractDTO
{
    public function __construct(
        public string $holderName,
        public string $number,
        public string $expiryMonth,
        public string $expiryYear,
        public string $cvv,
        public string $name,
        public string $email,
        public string $cpf,
        public string $postalCode,
        public string $addressNumber,
        public string $phone
    ) {
    }

    public static function FromArray(array $data): self
    {
        return new self(
            holderName: $data['holder_name'],
            number: $data['number'],
            expiryMonth: $data['expiry_month'],
            expiryYear: $data['expiry_year'],
            cvv: $data['cvv'],
            name: $data['name'],
            email: $data['email'],
            cpf: $data['cpf'],
            postalCode: $data['postal_code'],
            addressNumber: $data['address_number'],
            phone: $data['phone']
        );
    }
}
