<?php

namespace App\Application\ModerationEventRouting;

use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;

final class DeterministicModerationEventRouter
{
    /** @return list<ModerationRoutingDestination> */
    public function route(ModerationEventV1 $event): array
    {
        return match ($event->type) {
            ModerationEventTypeV1::ReportSubmitted,
            ModerationEventTypeV1::ReportValidated => [
                ModerationRoutingDestination::QueueProjection,
                ModerationRoutingDestination::CaseTimeline,
                ModerationRoutingDestination::DeliveryObservation,
            ],
            ModerationEventTypeV1::DecisionIssued => [
                ModerationRoutingDestination::CaseTimeline,
                ModerationRoutingDestination::DeliveryObservation,
                ModerationRoutingDestination::ListingHandoff,
            ],
            ModerationEventTypeV1::CaseClosed => [ModerationRoutingDestination::CaseTimeline, ModerationRoutingDestination::DeliveryObservation],
            ModerationEventTypeV1::TargetActionCompleted => [ModerationRoutingDestination::QueueProjection, ModerationRoutingDestination::DeliveryObservation],
        };
    }
}
