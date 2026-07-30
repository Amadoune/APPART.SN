<?php

namespace Appart\Modules\ListingLifecycle\Domain\ValueObject;

use Appart\Modules\ListingLifecycle\Domain\Exception\InvalidListingValue;

final readonly class ActorId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 100) {
            throw InvalidListingValue::field('actor_id');
        }

        return new self($value);
    }
}
