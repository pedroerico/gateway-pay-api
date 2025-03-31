<?php

namespace App\Enums;

use App\Traits\EnumsToArrayTrait;

enum PaymentMethodEnum: string
{
    use EnumsToArrayTrait;

    case CREDIT_CARD = 'credit_card';
    case BOLETO = 'boleto';
    case PIX = 'pix';
}
