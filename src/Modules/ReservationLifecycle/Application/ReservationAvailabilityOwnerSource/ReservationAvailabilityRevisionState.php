<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class ReservationAvailabilityRevisionState
{
    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(
        public ReservationAvailabilityIntentId $intentId,
        public int $revision,
        public ReservationAvailabilitySubjectId $subjectId,
        public ReservationAvailabilityWindow $window,
        public ReservationAvailabilityRevisionDecision $decision,
        DateTimeImmutable $effectiveAt,
        DateTimeImmutable $recordedAt,
    ) {
        if ($revision < 1) {
            throw new InvalidArgumentException('Reservation availability revision must be positive.');
        }

        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Reservation availability revision cannot be recorded before it takes effect.');
        }
    }
}
