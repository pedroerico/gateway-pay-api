<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\ApiClientCustomer;
use App\Models\GatewayCustomer;

class PaymentDTO extends AbstractDTO
{
    public function __construct(
        public float $amount,
        public PaymentMethodEnum $method,
        public PaymentStatusEnum $status,
        public ?int $externalId,
        public ?array $metadata = null,
        public ?CustomerDTO $customer = null,
        public ?AddressDTO $address,
        public ?CreditCardDTO $creditCard = null,
        public ?GatewayCustomer $gatewayCustomer = null,
        public ?ApiClientCustomer $apiClientCustomer = null,
    ) {
    }

    public static function FromRequest(array $data): self
    {
        return new self(
            amount: (float)$data['amount'],
            method: PaymentMethodEnum::tryFrom($data['method']),
            status: PaymentStatusEnum::PROCESSING,
            externalId: $data['external_id'] ?? null,
            metadata: $data['metadata'] ?? null,
            customer: isset($data['customer']) ? CustomerDTO::FromArray($data['customer']) : null,
            address: isset($data['address']) ? AddressDTO::FromArray($data['address']) : null,
            creditCard: $data['method'] === PaymentMethodEnum::CREDIT_CARD->value ?
                CreditCardDTO::FromArray($data['card'])
                : null,
        );
    }

    public function create(): CreatePaymentDTO
    {
        return new CreatePaymentDTO(
            amount: $this->amount,
            method:  $this->method->value,
            status: $this->status->value,
            apiClientCustomerId: $this->apiClientCustomer->id ?? null,
            metadata: $this->metadata,
        );
    }
}
