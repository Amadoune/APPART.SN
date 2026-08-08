<?php

namespace Tests\Unit\ReservationAvailabilityOwnerReader;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerReader\OwnerReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadAvailability;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityStatusV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OwnerReservationAvailabilityReaderTest extends TestCase
{
    #[DataProvider('mappingCases')]
    public function test_mapping_is_exhaustive_and_mechanical(
        ReservationAvailabilityOwnerSourceRuntimeReadResult $runtimeResult,
        ReservationAvailabilityStatusV1 $expected,
    ): void {
        $reader = new OwnerReservationAvailabilityReaderV1(new OwnerReaderRuntimeReadStub($runtimeResult));

        self::assertSame(
            $expected,
            $reader->read(
                ReservationAvailabilityIntentId::fromString('00000000-0000-4000-8000-000000005417'),
                ReservationAvailabilitySubjectId::fromString('00000000-0000-4000-8000-000000005418'),
                new ReservationAvailabilityWindow(
                    new DateTimeImmutable('2026-08-10T08:00:00Z'),
                    new DateTimeImmutable('2026-08-10T09:00:00Z'),
                ),
                new ReservationAvailabilityObservedAt(new DateTimeImmutable('2026-08-01T08:00:00Z')),
            )->status,
        );
    }

    /** @return iterable<string, array{ReservationAvailabilityOwnerSourceRuntimeReadResult, ReservationAvailabilityStatusV1}> */
    public static function mappingCases(): iterable
    {
        yield 'allowed to available' => [ReservationAvailabilityOwnerSourceRuntimeReadResult::allowed(), ReservationAvailabilityStatusV1::Available];
        yield 'conflicting' => [ReservationAvailabilityOwnerSourceRuntimeReadResult::conflicting(), ReservationAvailabilityStatusV1::Conflicting];
        yield 'missing' => [ReservationAvailabilityOwnerSourceRuntimeReadResult::missing(), ReservationAvailabilityStatusV1::Missing];
        yield 'corrupted' => [ReservationAvailabilityOwnerSourceRuntimeReadResult::corrupted(), ReservationAvailabilityStatusV1::Corrupted];
        yield 'unavailable' => [ReservationAvailabilityOwnerSourceRuntimeReadResult::dependencyUnavailable(), ReservationAvailabilityStatusV1::DependencyUnavailable];
    }
}

final readonly class OwnerReaderRuntimeReadStub implements ReservationAvailabilityOwnerSourceRuntimeReadV1
{
    public function __construct(private ReservationAvailabilityOwnerSourceRuntimeReadResult $result) {}

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityOwnerSourceRuntimeReadResult {
        return $this->result;
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
