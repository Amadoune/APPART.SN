<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum Currency: string
{
    case XOF = 'XOF';
    case EUR = 'EUR';
    case USD = 'USD';
}
