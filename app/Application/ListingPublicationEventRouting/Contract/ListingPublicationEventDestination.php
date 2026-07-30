<?php

namespace App\Application\ListingPublicationEventRouting\Contract;

use App\Application\ListingPublicationEventRouting\ListingPublicationEventDestinationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;

interface ListingPublicationEventDestination
{
    public function transfer(ListingPublicationEvent $event): ListingPublicationEventDestinationResult;
}
