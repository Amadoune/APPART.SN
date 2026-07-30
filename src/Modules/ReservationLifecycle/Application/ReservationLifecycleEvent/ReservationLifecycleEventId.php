<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use InvalidArgumentException;

final readonly class ReservationLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        ReservationLifecycleEventType $eventType,
        ReservationLifecycleEventPayloadVersion $payloadVersion,
        ReservationId $reservationId,
        ReservationLifecycleTransition $transition,
        int $occurredVersion,
    ): self {
        if ($occurredVersion < 1) {
            throw new InvalidArgumentException('Reservation lifecycle occurred version must be positive.');
        }

        return new self('reservation-lifecycle-'.hash('sha256', implode('|', [
            ReservationLifecycleEventAggregateType::ReservationLifecycle->value,
            $eventType->value,
            (string) $payloadVersion->value,
            $reservationId->value,
            self::transition($transition),
            $transition->from->value,
            $transition->to->value,
            $transition->action->value,
            (string) $payloadVersion->value,
            (string) $occurredVersion,
        ])));
    }

    private static function transition(ReservationLifecycleTransition $transition): string
    {
        return implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]);
    }
}
