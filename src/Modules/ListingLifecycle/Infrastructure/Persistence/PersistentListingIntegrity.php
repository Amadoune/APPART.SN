<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use RuntimeException;

final class PersistentListingIntegrity extends RuntimeException
{
    public static function invalid(string $component): self
    {
        return new self('Persistent Listing data is inconsistent: '.$component.'.');
    }
}
