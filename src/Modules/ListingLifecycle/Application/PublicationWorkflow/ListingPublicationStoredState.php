<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

final readonly class ListingPublicationStoredState
{
    public function __construct(
        public ListingId $listingId,
        public ListingPublicationState $state,
        public int $version,
    ) {}
}
