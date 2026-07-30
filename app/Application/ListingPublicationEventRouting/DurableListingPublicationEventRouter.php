<?php

namespace App\Application\ListingPublicationEventRouting;

use App\Application\ListingPublicationEventRouting\Contract\ListingPublicationEventDestination;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingDiagnosticCode;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;

final readonly class DurableListingPublicationEventRouter implements ListingPublicationEventRouter
{
    public function __construct(private ListingPublicationEventDestination $destination) {}

    public function route(ListingPublicationEvent $event): ListingPublicationEventRoutingResult
    {
        return match ($this->destination->transfer($event)->status) {
            ListingPublicationEventDestinationStatus::Stored,
            ListingPublicationEventDestinationStatus::AlreadyStored => ListingPublicationEventRoutingResult::routed(),
            ListingPublicationEventDestinationStatus::Unavailable => ListingPublicationEventRoutingResult::deferred(ListingPublicationEventRoutingDiagnosticCode::RouteUnavailable),
            ListingPublicationEventDestinationStatus::RetryableFailure => ListingPublicationEventRoutingResult::retryableFailure(ListingPublicationEventRoutingDiagnosticCode::TransferFailed),
            ListingPublicationEventDestinationStatus::Rejected => ListingPublicationEventRoutingResult::rejected(ListingPublicationEventRoutingDiagnosticCode::CorruptedEvent),
        };
    }
}
