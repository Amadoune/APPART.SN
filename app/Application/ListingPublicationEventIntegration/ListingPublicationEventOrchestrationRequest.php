<?php

namespace App\Application\ListingPublicationEventIntegration;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;

final readonly class ListingPublicationEventOrchestrationRequest
{
    public function __construct(
        public ListingPublicationOrchestrationRequest $transition,
        public ListingPublicationEventMetadata $metadata,
    ) {}
}
