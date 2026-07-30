<?php

namespace App\Application\PublicProjectionRebuild;

use InvalidArgumentException;

final readonly class PublicProjectionRebuildScope
{
    /** @param list<string> $listingIds */
    private function __construct(public PublicProjectionRebuildScopeType $type, public array $listingIds, public ?string $from, public ?string $to) {}

    public static function full(): self
    {
        return new self(PublicProjectionRebuildScopeType::Full, [], null, null);
    }

    /** @param list<string> $listingIds */
    public static function listings(array $listingIds): self
    {
        $listingIds = array_values(array_unique(array_map('trim', $listingIds)));
        if ($listingIds === [] || in_array('', $listingIds, true)) {
            throw new InvalidArgumentException('A partial rebuild requires non-empty listing identities.');
        }

        return new self(PublicProjectionRebuildScopeType::Listings, $listingIds, null, null);
    }

    public static function range(string $from, string $to): self
    {
        $from = trim($from);
        $to = trim($to);
        if ($from === '' || $to === '' || strcmp($from, $to) > 0) {
            throw new InvalidArgumentException('A rebuild range must be ordered and bounded.');
        }

        return new self(PublicProjectionRebuildScopeType::Range, [], $from, $to);
    }
}
