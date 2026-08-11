<?php

namespace Appart\Modules\PublicationReview\Application\Queue\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewIngestionResult;

interface PublicationReviewQueue
{
    public function ingest(ListingPublicationEvent $event): PublicationReviewIngestionResult;
}
