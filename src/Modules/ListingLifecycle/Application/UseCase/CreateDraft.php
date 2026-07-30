<?php

namespace Appart\Modules\ListingLifecycle\Application\UseCase;

use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\Contract\PropertyCatalog;
use Appart\Modules\ListingLifecycle\Domain\Model\Listing;
use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;

final readonly class CreateDraft
{
    public function __construct(private ListingRegistry $listings, private PropertyCatalog $properties, private ListingTransitionPolicy $policy) {}

    public function execute(ListingId $id, PropertyId $propertyId, ListingRevisionId $revisionId, TransitionEvidence $evidence): Listing
    {
        $listing = Listing::createDraft($id, $propertyId, $revisionId, $evidence, $this->policy, $this->properties->availabilityOf($propertyId));
        $this->listings->add($listing);

        return $listing;
    }
}
