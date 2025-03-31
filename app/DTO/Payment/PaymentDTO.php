<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;

class PaymentDTO extends AbstractDTO
{
    public function __construct(
        public float $amount,
        public PaymentMethodEnum $method,
        public PaymentStatusEnum $status,
        public ?array $metadata = null,
        public ?CreditCardDTO $creditCard = null,
        public ?CustomerDTO $customer = null
    ) {
    }

    public static function FromRequest(array $data): self
    {
        return new self(
            amount: (float)$data['amount'],
            method: PaymentMethodEnum::tryFrom($data['method']),
            status: PaymentStatusEnum::PROCESSING,
            metadata: $data['metadata'] ?? null,
            creditCard: $data['method'] === PaymentMethodEnum::CREDIT_CARD->value ?
                CreditCardDTO::FromArray($data['card'])
                : null,
            customer: $data['method'] === PaymentMethodEnum::CREDIT_CARD->value ?
                CustomerDTO::FromArray($data['customer'])
                : null
        );
    }

    public function create(): array
    {
        return [
            'amount' => $this->amount,
            'method' => $this->method->value,
            'status' => $this->status->value,
            'metadata' => $this->metadata,
        ];
    }
}
