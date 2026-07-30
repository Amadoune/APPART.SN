<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence;

final readonly class ReservationLifecyclePersistenceReadResult
{
    private function __construct(
        public ReservationId $reservationId,
        public ReservationLifecyclePersistenceReadStatus $status,
        public ?ReservationLifecycleStoredState $snapshot,
    ) {}

    public static function found(ReservationLifecycleStoredState $snapshot): self
    {
        return new self($snapshot->reservationId, ReservationLifecyclePersistenceReadStatus::Found, $snapshot);
    }

    public static function missing(ReservationId $reservationId): self
    {
        return new self($reservationId, ReservationLifecyclePersistenceReadStatus::Missing, null);
    }

    public static function corrupted(ReservationId $reservationId): self
    {
        return new self($reservationId, ReservationLifecyclePersistenceReadStatus::Corrupted, null);
    }
}
