<?php

namespace Appart\Modules\ListingLifecycle\Domain\Event;

use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ExpirationDate;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingStatus;

final readonly class ListingPublished extends AbstractListingEvent
{
    public function __construct(ListingId $id, ListingRevisionId $revisionId, ListingStatus $previous, public ExpirationDate $expirationDate, TransitionEvidence $evidence)
    {
        parent::__construct($id, $revisionId, $previous, $evidence);
    }
}
