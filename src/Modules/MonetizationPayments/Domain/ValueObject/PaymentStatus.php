<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum PaymentStatus: string
{
    case Authorized = 'authorized';
    case Captured = 'captured';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
