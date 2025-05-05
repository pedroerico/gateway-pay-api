<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;

class CustomerDTO extends AbstractDTO
{
    public function __construct(
        public string $cpf,
        public ?string $name,
        public ?string $email,
        public ?string $phone,
    ) {
    }

    public static function FromArray(array $data): self
    {
        return new self(
            cpf: $data['cpf'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }
}
