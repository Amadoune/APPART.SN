<?php

namespace Appart\Modules\ListingLifecycle\Domain\Exception;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;

final class ListingViolation extends ListingException
{
    public static function transition(ListingStatus $from, ListingStatus $to): self
    {
        return new self("Transition from {$from->value} to {$to->value} is forbidden.");
    }

    public static function duplicateRevision(): self
    {
        return new self('Listing revision identity already exists.');
    }

    public static function expirationRequired(): self
    {
        return new self('A future expiration date is required for publication.');
    }

    public static function expirationNotReached(): self
    {
        return new self('The listing cannot expire before its expiration date.');
    }
}
