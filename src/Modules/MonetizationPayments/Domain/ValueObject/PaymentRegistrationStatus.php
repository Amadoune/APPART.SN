<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum PaymentRegistrationStatus: string
{
    case Created = 'created';
    case ExistingIdempotent = 'existing_idempotent';
}
