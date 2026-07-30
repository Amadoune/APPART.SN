<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use Appart\Modules\MonetizationPayments\Domain\Exception\InvalidPaymentValue;
use DateTimeImmutable;

final readonly class BenefitPeriod
{
    public function __construct(public DateTimeImmutable $startsAt, public DateTimeImmutable $endsAt)
    {
        if ($endsAt <= $startsAt) {
            throw InvalidPaymentValue::field('benefit_period');
        }
    }
}
