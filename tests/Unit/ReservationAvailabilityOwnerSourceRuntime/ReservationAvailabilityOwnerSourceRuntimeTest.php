<?php

namespace Tests\Unit\ReservationAvailabilityOwnerSourceRuntime;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionState;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityWriteResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\ReservationAvailabilityOwnerSourceRuntimeAvailability;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReservationAvailabilityOwnerSourceRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_runtime_policy_is_deterministic_and_fail_closed(
        ReservationAvailabilityReadResult $readResult,
        ReservationAvailabilityOwnerSourceRuntimeAvailability $expected,
    ): void {
        $runtime = $this->runtime(new ReservationRuntimeSourceStub($readResult));

        self::assertSame($expected, $runtime->availability());
        self::assertSame($expected, $runtime->diagnostics()->availability);
        self::assertSame('reservation-lifecycle.availability-owner-source', $runtime->diagnostics()->runtimeId);
        self::assertSame('reservation-availability-owner-source-runtime-v1', $runtime->diagnostics()->version);
    }

    public function test_exception_becomes_dependency_unavailable_without_diagnostic_leak(): void
    {
        $runtime = $this->runtime(new ThrowingReservationRuntimeSourceStub);

        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame(
            ['reservation-lifecycle.availability-owner-source', 'reservation-availability-owner-source-runtime-v1', 'dependency_unavailable'],
            [
                $runtime->diagnostics()->runtimeId,
                $runtime->diagnostics()->version,
                $runtime->diagnostics()->availability->value,
            ],
        );
    }

    /** @return iterable<string, array{ReservationAvailabilityReadResult, ReservationAvailabilityOwnerSourceRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        $revision = ReservationAvailabilityRuntimeFixture::revision();

        yield 'found available' => [ReservationAvailabilityReadResult::available($revision), ReservationAvailabilityOwnerSourceRuntimeAvailability::Available];
        yield 'found conflicting' => [ReservationAvailabilityReadResult::conflicting($revision), ReservationAvailabilityOwnerSourceRuntimeAvailability::Available];
        yield 'missing' => [ReservationAvailabilityReadResult::missing($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeAvailability::Available];
        yield 'corrupted' => [ReservationAvailabilityReadResult::corrupted($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeAvailability::Corrupted];
        yield 'unavailable' => [ReservationAvailabilityReadResult::dependencyUnavailable($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeAvailability::DependencyUnavailable];
    }

    private function runtime(ReservationAvailabilityOwnerSource $source): DeterministicReservationAvailabilityOwnerSourceRuntimeV1
    {
        return new DeterministicReservationAvailabilityOwnerSourceRuntimeV1(
            new DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy($source),
        );
    }
}

final readonly class ReservationRuntimeSourceStub implements ReservationAvailabilityOwnerSource
{
    public function __construct(private ReservationAvailabilityReadResult $result) {}

    public function append(ReservationAvailabilityRevisionState $revision): ReservationAvailabilityWriteResult
    {
        return ReservationAvailabilityWriteResult::DependencyUnavailable;
    }

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityReadResult {
        return $this->result;
    }

    public function history(ReservationAvailabilityIntentId $intentId): array
    {
        return [];
    }
}

final readonly class ThrowingReservationRuntimeSourceStub implements ReservationAvailabilityOwnerSource
{
    public function append(ReservationAvailabilityRevisionState $revision): ReservationAvailabilityWriteResult
    {
        throw new RuntimeException('secret infrastructure diagnostic');
    }

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityReadResult {
        throw new RuntimeException('secret infrastructure diagnostic');
    }

    public function history(ReservationAvailabilityIntentId $intentId): array
    {
        throw new RuntimeException('secret infrastructure diagnostic');
    }
}

final class ReservationAvailabilityRuntimeFixture
{
    public static function revision(): ReservationAvailabilityRevisionState
    {
        $effectiveAt = new DateTimeImmutable('2026-08-01T08:00:00Z');

        return new ReservationAvailabilityRevisionState(
            ReservationAvailabilityIntentId::fromString('00000000-0000-4000-8000-000000005409'),
            1,
            ReservationAvailabilitySubjectId::fromString('00000000-0000-4000-8000-000000005410'),
            new ReservationAvailabilityWindow(
                new DateTimeImmutable('2026-08-02T08:00:00Z'),
                new DateTimeImmutable('2026-08-02T09:00:00Z'),
            ),
            ReservationAvailabilityRevisionDecision::Proposed,
            $effectiveAt,
            $effectiveAt,
        );
    }
}
