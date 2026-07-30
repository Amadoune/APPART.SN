<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;

interface ListingPublicationOrchestrator
{
    public function transition(ListingPublicationOrchestrationRequest $request): ListingPublicationOrchestrationResult;
}
