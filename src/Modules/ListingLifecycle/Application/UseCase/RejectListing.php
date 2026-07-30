<?php

namespace Appart\Modules\ListingLifecycle\Application\UseCase;

use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;

final readonly class RejectListing extends ListingUseCase
{
    public function execute(ListingId $id, ListingRevisionId $revisionId, TransitionEvidence $evidence): void
    {
        [$listing,$version] = $this->load($id);
        $listing->reject($revisionId, $evidence, $this->policy, $this->properties->availabilityOf($listing->propertyId()));
        $this->listings->save($listing, $version);
    }
}
