<?php

namespace Appart\Modules\ListingLifecycle\Domain\Exception;

final class InvalidListingValue extends ListingException
{
    public static function field(string $field): self
    {
        return new self("Invalid listing value: {$field}.");
    }
}
