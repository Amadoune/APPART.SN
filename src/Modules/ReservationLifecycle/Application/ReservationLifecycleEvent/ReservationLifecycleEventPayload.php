<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use InvalidArgumentException;

final readonly class ReservationLifecycleEventPayload
{
    public function __construct(
        public ReservationLifecycleEventId $eventId,
        public ReservationLifecycleEventAggregateType $aggregateType,
        public ReservationId $reservationId,
        public string $transition,
        public ReservationLifecycleState $previousState,
        public ReservationLifecycleState $currentState,
        public ReservationLifecycleAction $action,
        public int $version,
        public int $occurredVersion,
    ) {
        if ($version !== ReservationLifecycleEventPayloadVersion::V1->value) {
            throw new InvalidArgumentException('Reservation lifecycle event payload version must be V1.');
        }
        if ($occurredVersion < 1) {
            throw new InvalidArgumentException('Reservation lifecycle occurred version must be positive.');
        }
        if ($transition !== implode('>', [$previousState->value, $action->value, $currentState->value])) {
            throw new InvalidArgumentException('Reservation lifecycle transition representation is inconsistent.');
        }
    }

    /** @return array{eventId:string,aggregateType:string,reservationId:string,transition:string,previousState:string,currentState:string,action:string,version:int,occurredVersion:int} */
    public function fields(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'aggregateType' => $this->aggregateType->value,
            'reservationId' => $this->reservationId->value,
            'transition' => $this->transition,
            'previousState' => $this->previousState->value,
            'currentState' => $this->currentState->value,
            'action' => $this->action->value,
            'version' => $this->version,
            'occurredVersion' => $this->occurredVersion,
        ];
    }
}
