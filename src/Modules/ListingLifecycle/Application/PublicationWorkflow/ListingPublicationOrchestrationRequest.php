<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use InvalidArgumentException;

final readonly class ListingPublicationOrchestrationRequest
{
    public function __construct(
        public ListingId $listingId,
        public ListingPublicationAction $action,
        public int $expectedVersion,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected listing publication version must be positive.');
        }
    }
}
