<?php

namespace Appart\Modules\ListingLifecycle\Domain\Event;

use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;

final readonly class ListingDraftCreated extends AbstractListingEvent
{
    public function __construct(ListingId $id, ListingRevisionId $revisionId, public PropertyId $propertyId, TransitionEvidence $evidence)
    {
        parent::__construct($id, $revisionId, null, $evidence);
    }
}
