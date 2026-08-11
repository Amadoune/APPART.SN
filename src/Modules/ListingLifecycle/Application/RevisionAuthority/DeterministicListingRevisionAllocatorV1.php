<?php

namespace Appart\Modules\ListingLifecycle\Application\RevisionAuthority;

use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract\ListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;

final readonly class DeterministicListingRevisionAllocatorV1 implements ListingRevisionAllocatorV1
{
    private const string SCOPE = 'appart.listing-lifecycle.revision.v1';

    public function allocate(ListingId $listingId, ListingRevisionOperation $operation, ListingRevisionIntentId $intentId): ListingRevisionId
    {
        $hex = substr(hash('sha256', implode("\n", [
            self::SCOPE,
            $listingId->value,
            $operation->value,
            $intentId->value,
        ])), 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 0x3) | 0x8);

        return ListingRevisionId::fromString(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        ));
    }
}
