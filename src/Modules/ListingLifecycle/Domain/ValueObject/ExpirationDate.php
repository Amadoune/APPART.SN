<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;
use DateTimeImmutable;

final readonly class ExpirationDate
{
    private function __construct(public DateTimeImmutable $value) {}

    public static function fromDateTime(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function ensureAfter(DateTimeImmutable $publicationTime): void
    {
        if ($this->value <= $publicationTime) {
            throw InvalidListingValue::field('expiration_date');
        }
    }
}
