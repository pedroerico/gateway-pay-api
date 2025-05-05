<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;

class AddressDTO extends AbstractDTO
{
    public function __construct(
        public string $zipCode,
        public string $street,
        public string $number,
        public string $neighborhood,
        public string $city,
        public string $state
    ) {
    }

    public static function FromArray(array $data): self
    {
        return new self(
            zipCode: $data['zip_code'],
            street: $data['street'],
            number: $data['number'],
            neighborhood: $data['neighborhood'],
            city: $data['city'],
            state: $data['state']
        );
    }
}
