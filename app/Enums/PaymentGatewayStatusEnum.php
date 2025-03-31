<?php

namespace App\Enums;

use App\Traits\EnumsToArrayTrait;

enum PaymentGatewayStatusEnum: string
{
    use EnumsToArrayTrait;

    case AUTHORIZED = 'AUTHORIZED';
    case CONFIRMED = 'CONFIRMED';
    case DELETED = 'DELETED';
    case REFUNDED = 'REFUNDED';
    case RECEIVED = 'RECEIVED';
    case OVERDUE = 'OVERDUE';
    case PAYMENT_DELETED = 'PAYMENT_DELETED';
}
