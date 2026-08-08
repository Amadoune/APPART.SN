<?php

namespace Tests\Feature;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerReader\OwnerReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadAvailability;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\Contract\ReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use Tests\TestCase;

final class ReservationAvailabilityOwnerReaderCompositionTest extends TestCase
{
    public function test_reader_binding_is_lazy_unique_and_singleton(): void
    {
        $this->app->instance(ReservationAvailabilityOwnerSourceRuntimeReadV1::class, new ReservationAvailabilityOwnerReaderCompositionRuntimeReadStub);

        self::assertTrue($this->app->bound(ReservationAvailabilityReaderV1::class));
        self::assertFalse($this->app->resolved(ReservationAvailabilityReaderV1::class));

        $reader = $this->app->make(ReservationAvailabilityReaderV1::class);
        self::assertInstanceOf(OwnerReservationAvailabilityReaderV1::class, $reader);
        self::assertSame($reader, $this->app->make(ReservationAvailabilityReaderV1::class));
    }
}

final readonly class ReservationAvailabilityOwnerReaderCompositionRuntimeReadStub implements ReservationAvailabilityOwnerSourceRuntimeReadV1
{
    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityOwnerSourceRuntimeReadResult {
        return ReservationAvailabilityOwnerSourceRuntimeReadResult::missing();
    }

    public function diagnostics(): ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics
    {
        return new ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics(
            'stub',
            'v1',
            ReservationAvailabilityOwnerSourceRuntimeReadAvailability::Available,
        );
    }
}
