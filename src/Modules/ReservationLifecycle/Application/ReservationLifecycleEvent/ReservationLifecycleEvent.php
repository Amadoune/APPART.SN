<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use InvalidArgumentException;

final readonly class ReservationLifecycleEvent
{
    public function __construct(
        public ReservationLifecycleEventMetadata $metadata,
        public ReservationLifecycleEventPayload $payload,
    ) {
        if ($metadata->payloadVersion->value !== $payload->version) {
            throw new InvalidArgumentException('Reservation lifecycle event payload versions are inconsistent.');
        }

        $expectedId = ReservationLifecycleEventId::derive(
            $metadata->eventType,
            $metadata->payloadVersion,
            $payload->reservationId,
            new ReservationLifecycleTransition($payload->previousState, $payload->currentState, $payload->action),
            $payload->occurredVersion,
        );
        if ($expectedId->value !== $payload->eventId->value) {
            throw new InvalidArgumentException('Reservation lifecycle event identity is inconsistent.');
        }
    }
}
