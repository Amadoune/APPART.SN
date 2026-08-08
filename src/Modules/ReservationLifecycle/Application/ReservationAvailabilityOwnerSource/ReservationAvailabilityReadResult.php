<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use LogicException;

final readonly class ReservationAvailabilityReadResult
{
    private function __construct(
        public ReservationAvailabilityIntentId $intentId,
        public ReservationAvailabilityReadStatus $status,
        public ?ReservationAvailabilityRevisionState $revision,
    ) {
        $hasRevision = $revision !== null;
        $resolved = $status === ReservationAvailabilityReadStatus::Available
            || $status === ReservationAvailabilityReadStatus::Conflicting;
        if ($hasRevision !== $resolved) {
            throw new LogicException('Only resolved reservation availability reads may expose a revision.');
        }
    }

    public static function available(ReservationAvailabilityRevisionState $revision): self
    {
        return new self($revision->intentId, ReservationAvailabilityReadStatus::Available, $revision);
    }

    public static function conflicting(ReservationAvailabilityRevisionState $revision): self
    {
        return new self($revision->intentId, ReservationAvailabilityReadStatus::Conflicting, $revision);
    }

    public static function missing(ReservationAvailabilityIntentId $intentId): self
    {
        return new self($intentId, ReservationAvailabilityReadStatus::Missing, null);
    }

    public static function corrupted(ReservationAvailabilityIntentId $intentId): self
    {
        return new self($intentId, ReservationAvailabilityReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(ReservationAvailabilityIntentId $intentId): self
    {
        return new self($intentId, ReservationAvailabilityReadStatus::DependencyUnavailable, null);
    }
}
