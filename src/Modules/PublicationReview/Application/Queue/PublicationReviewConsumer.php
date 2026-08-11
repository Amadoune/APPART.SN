<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueue;

final readonly class PublicationReviewConsumer
{
    public function __construct(private PublicationReviewQueue $queue) {}

    public function consume(ListingPublicationEvent $event): PublicationReviewIngestionResult
    {
        return $this->queue->ingest($event);
    }
}
