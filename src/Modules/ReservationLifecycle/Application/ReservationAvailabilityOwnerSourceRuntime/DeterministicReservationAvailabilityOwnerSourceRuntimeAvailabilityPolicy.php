<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy implements ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy
{
    private const PROBE_INTENT_ID = '00000000-0000-4000-8000-000000005409';

    private const PROBE_SUBJECT_ID = '00000000-0000-4000-8000-000000005410';

    public function __construct(private ReservationAvailabilityOwnerSource $source) {}

    public function inspect(): ReservationAvailabilityOwnerSourceRuntimeAvailability
    {
        try {
            $result = $this->source->read(
                ReservationAvailabilityIntentId::fromString(self::PROBE_INTENT_ID),
                ReservationAvailabilitySubjectId::fromString(self::PROBE_SUBJECT_ID),
                new ReservationAvailabilityWindow(
                    new DateTimeImmutable('9999-12-30T00:00:00.000000Z'),
                    new DateTimeImmutable('9999-12-30T01:00:00.000000Z'),
                ),
                new ReservationAvailabilityObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                ReservationAvailabilityReadStatus::Available,
                ReservationAvailabilityReadStatus::Conflicting,
                ReservationAvailabilityReadStatus::Missing => ReservationAvailabilityOwnerSourceRuntimeAvailability::Available,
                ReservationAvailabilityReadStatus::Corrupted => ReservationAvailabilityOwnerSourceRuntimeAvailability::Corrupted,
                ReservationAvailabilityReadStatus::DependencyUnavailable => ReservationAvailabilityOwnerSourceRuntimeAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return ReservationAvailabilityOwnerSourceRuntimeAvailability::DependencyUnavailable;
        }
    }
}
