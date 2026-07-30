<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

use DateTimeImmutable;

final readonly class OccurredAt
{
    public function __construct(public DateTimeImmutable $value) {}

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }
}
