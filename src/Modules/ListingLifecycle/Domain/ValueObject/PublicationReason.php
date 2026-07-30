<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;

final readonly class PublicationReason
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 3 || mb_strlen($value) > 500) {
            throw InvalidListingValue::field('publication_reason');
        }

        return new self($value);
    }
}
