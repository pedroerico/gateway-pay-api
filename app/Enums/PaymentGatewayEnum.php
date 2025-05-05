<?php

namespace App\Enums;

use App\Traits\EnumsToArrayTrait;

enum PaymentGatewayEnum: string
{
    use EnumsToArrayTrait;

    case ASAAS = 'asaas';
    case PAGBANK = 'pag_bank';
    case MERCADO_PAGO = 'mercado_pago';
}
