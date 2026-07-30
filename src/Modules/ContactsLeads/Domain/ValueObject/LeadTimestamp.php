<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use DateTimeImmutable;

final readonly class LeadTimestamp
{
    private function __construct(public DateTimeImmutable $value) {}

    public static function at(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function equals(self $other): bool
    {
        return $this->value == $other->value;
    }
}
