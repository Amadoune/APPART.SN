<?php

namespace App\Application\ReservationLifecycleEventIntegration;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ReservationLifecycleAtomicEventRequest
{
    public function __construct(
        public ReservationId $reservationId,
        public ReservationLifecycleAction $action,
        public int $expectedVersion,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $recordedAt,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected reservation lifecycle version must be positive.');
        }
    }

    public function transitionRequest(): ReservationLifecycleOrchestrationRequest
    {
        return new ReservationLifecycleOrchestrationRequest($this->reservationId, $this->action, $this->expectedVersion);
    }
}
