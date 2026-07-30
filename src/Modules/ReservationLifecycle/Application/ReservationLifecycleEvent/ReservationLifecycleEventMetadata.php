<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

final readonly class ReservationLifecycleEventMetadata
{
    public function __construct(
        public ReservationLifecycleEventType $eventType,
        public ReservationLifecycleEventPayloadVersion $payloadVersion,
    ) {}
}
