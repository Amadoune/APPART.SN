<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class LeadLifecycleOccurredAt
{
    private function __construct(public DateTimeImmutable $value) {}

    public static function fromExplicitUtc(DateTimeImmutable $value): self
    {
        if ($value->format('P') !== '+00:00') {
            throw new InvalidArgumentException('The lead lifecycle occurrence instant must be explicit UTC.');
        }

        return new self($value);
    }
}
