<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

final readonly class ReservationLifecycleEventSerializer
{
    public function serialize(ReservationLifecycleEvent $event): string
    {
        $payload = $event->payload->fields();

        return json_encode([
            'eventId' => $payload['eventId'],
            'aggregateType' => $payload['aggregateType'],
            'eventType' => $event->metadata->eventType->value,
            'payloadVersion' => $event->metadata->payloadVersion->value,
            'reservationId' => $payload['reservationId'],
            'transition' => $payload['transition'],
            'previousState' => $payload['previousState'],
            'currentState' => $payload['currentState'],
            'action' => $payload['action'],
            'version' => $payload['version'],
            'occurredVersion' => $payload['occurredVersion'],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
