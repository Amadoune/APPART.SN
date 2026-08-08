<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1 implements ReservationAvailabilityOwnerSourceRuntimeReadV1
{
    private const PROBE_INTENT_ID = '00000000-0000-4000-8000-000000005413';

    private const PROBE_SUBJECT_ID = '00000000-0000-4000-8000-000000005414';

    private const RUNTIME_READ_ID = 'reservation-lifecycle.availability-owner-source-runtime-read';

    private const VERSION = 'reservation-availability-owner-source-runtime-read-v1';

    public function __construct(
        private ReservationAvailabilityOwnerSource $source,
        private ReservationAvailabilityOwnerSourceRuntimeReadPolicy $policy,
    ) {}

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityOwnerSourceRuntimeReadResult {
        try {
            return $this->policy->reduce($this->source->read($intentId, $subjectId, $window, $observedAt));
        } catch (Throwable) {
            return ReservationAvailabilityOwnerSourceRuntimeReadResult::dependencyUnavailable();
        }
    }

    public function diagnostics(): ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics
    {
        return new ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics(
            self::RUNTIME_READ_ID,
            self::VERSION,
            $this->availability(),
        );
    }

    private function availability(): ReservationAvailabilityOwnerSourceRuntimeReadAvailability
    {
        try {
            $result = $this->source->read(
                ReservationAvailabilityIntentId::fromString(self::PROBE_INTENT_ID),
                ReservationAvailabilitySubjectId::fromString(self::PROBE_SUBJECT_ID),
                new ReservationAvailabilityWindow(
                    new DateTimeImmutable('9999-12-30T02:00:00.000000Z'),
                    new DateTimeImmutable('9999-12-30T03:00:00.000000Z'),
                ),
                new ReservationAvailabilityObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z')),
            );

            return match ($result->status) {
                ReservationAvailabilityReadStatus::Available,
                ReservationAvailabilityReadStatus::Conflicting,
                ReservationAvailabilityReadStatus::Missing => ReservationAvailabilityOwnerSourceRuntimeReadAvailability::Available,
                ReservationAvailabilityReadStatus::Corrupted => ReservationAvailabilityOwnerSourceRuntimeReadAvailability::Corrupted,
                ReservationAvailabilityReadStatus::DependencyUnavailable => ReservationAvailabilityOwnerSourceRuntimeReadAvailability::DependencyUnavailable,
            };
        } catch (Throwable) {
            return ReservationAvailabilityOwnerSourceRuntimeReadAvailability::DependencyUnavailable;
        }
    }
}
