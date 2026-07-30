<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum BenefitStatus: string
{
    case Granted = 'granted';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
