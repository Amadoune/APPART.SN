<?php

namespace App\Application\ModerationEventRouting;

use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\ModerationOperationalAuditEventTypeV1;
use InvalidArgumentException;

final class ModerationOperationalAuditDestinationMatrix
{
    /** @return list<ModerationRoutingDestination> */
    public function destinations(ModerationOperationalAuditOutboxMessageV1 $message): array
    {
        $event = $message->event;
        if ($event instanceof ModerationEventV1) {
            return match ($event->type) {
                ModerationEventTypeV1::ReportSubmitted,
                ModerationEventTypeV1::ReportValidated => [
                    ModerationRoutingDestination::QueueProjection,
                    ModerationRoutingDestination::CaseTimeline,
                    ModerationRoutingDestination::DeliveryObservation,
                ],
                default => throw new InvalidArgumentException('Unsupported operational audit Event V1.'),
            };
        }

        return match ($event->eventType()) {
            ModerationOperationalAuditEventTypeV1::FindingRecorded,
            ModerationOperationalAuditEventTypeV1::QueueItemClaimed => [
                ModerationRoutingDestination::DeliveryObservation,
            ],
        };
    }
}
