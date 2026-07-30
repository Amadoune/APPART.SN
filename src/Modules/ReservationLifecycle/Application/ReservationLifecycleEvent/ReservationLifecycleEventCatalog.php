<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;

final readonly class ReservationLifecycleEventCatalog
{
    /** @var array<string, ReservationLifecycleEventType> */
    private const array TRANSITION_EVENTS = [
        'draft>submit>requested' => ReservationLifecycleEventType::ReservationSubmitted,
        'draft>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromDraft,
        'requested>confirm>confirmed' => ReservationLifecycleEventType::ReservationConfirmed,
        'requested>reject>rejected' => ReservationLifecycleEventType::ReservationRejected,
        'requested>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromRequested,
        'requested>expire>expired' => ReservationLifecycleEventType::ReservationExpiredFromRequested,
        'confirmed>start>in_progress' => ReservationLifecycleEventType::ReservationStarted,
        'confirmed>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledFromConfirmed,
        'confirmed>expire>expired' => ReservationLifecycleEventType::ReservationExpiredFromConfirmed,
        'in_progress>complete>completed' => ReservationLifecycleEventType::ReservationCompleted,
        'in_progress>cancel>cancelled' => ReservationLifecycleEventType::ReservationCancelledInProgress,
    ];

    public function eventFor(ReservationId $reservationId, ReservationLifecycleTransition $transition, int $occurredVersion): ReservationLifecycleEvent
    {
        $transitionKey = self::transitionKey($transition);
        $eventType = self::TRANSITION_EVENTS[$transitionKey]
            ?? throw new UnsupportedReservationLifecycleEventTransition('The transition has no certified reservation lifecycle event mapping.');
        $payloadVersion = ReservationLifecycleEventPayloadVersion::V1;
        $eventId = ReservationLifecycleEventId::derive($eventType, $payloadVersion, $reservationId, $transition, $occurredVersion);

        return new ReservationLifecycleEvent(
            new ReservationLifecycleEventMetadata($eventType, $payloadVersion),
            new ReservationLifecycleEventPayload(
                $eventId,
                ReservationLifecycleEventAggregateType::ReservationLifecycle,
                $reservationId,
                $transitionKey,
                $transition->from,
                $transition->to,
                $transition->action,
                $payloadVersion->value,
                $occurredVersion,
            ),
        );
    }

    public function transitionCount(): int
    {
        return count(self::TRANSITION_EVENTS);
    }

    private static function transitionKey(ReservationLifecycleTransition $transition): string
    {
        return implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]);
    }
}
