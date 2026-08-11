<?php

namespace Appart\Modules\PublicationReview\Application\Projection\Contract;

use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;

interface PublicListingProjectionActivation
{
    public function activate(string $listingId): ProjectionActivationStatus;
}
