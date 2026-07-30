<?php

namespace App\Application\ListingPublicationEventTransport\Contract;

use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;

interface ListingPublicationEventRouter
{
    public function route(ListingPublicationEvent $event): ListingPublicationEventRoutingResult;
}
