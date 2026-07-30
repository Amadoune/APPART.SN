<?php

namespace App\Application\PropertyLifecycleEventRouting;

use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingDiagnosticCode;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;

final readonly class DurablePropertyLifecycleEventRouter implements PropertyLifecycleEventRouter
{
    public function __construct(private PropertyLifecycleEventDestination $destination) {}

    public function route(PropertyLifecycleEvent $event): PropertyLifecycleEventRoutingResult
    {
        return match ($this->destination->transfer($event)->status) {
            PropertyLifecycleEventDestinationStatus::Stored,
            PropertyLifecycleEventDestinationStatus::AlreadyStored => PropertyLifecycleEventRoutingResult::routed(),
            PropertyLifecycleEventDestinationStatus::Unavailable => PropertyLifecycleEventRoutingResult::deferred(PropertyLifecycleEventRoutingDiagnosticCode::RouteUnavailable),
            PropertyLifecycleEventDestinationStatus::RetryableFailure => PropertyLifecycleEventRoutingResult::retryableFailure(PropertyLifecycleEventRoutingDiagnosticCode::TransferFailed),
            PropertyLifecycleEventDestinationStatus::Rejected => PropertyLifecycleEventRoutingResult::rejected(PropertyLifecycleEventRoutingDiagnosticCode::CorruptedEvent),
        };
    }
}
