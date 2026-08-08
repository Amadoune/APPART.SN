<?php

namespace Tests\Unit\ReservationAvailabilityOwnerSourceRuntimeRead;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\Contract\ReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionState;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityWriteResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadAvailability;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReservationAvailabilityOwnerSourceRuntimeReadTest extends TestCase
{
    #[DataProvider('reductionCases')]
    public function test_policy_reduces_each_source_result_mechanically(
        ReservationAvailabilityReadResult $sourceResult,
        ReservationAvailabilityOwnerSourceRuntimeReadStatus $expected,
    ): void {
        $policy = new DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy;

        self::assertSame($expected, $policy->reduce($sourceResult)->status);
    }

    public function test_runtime_reads_and_exposes_closed_diagnostics(): void
    {
        $revision = RuntimeReadFixture::revision();
        $runtime = $this->runtime(new RuntimeReadSourceStub(ReservationAvailabilityReadResult::available($revision)));

        self::assertSame(
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::Allowed,
            $runtime->read($revision->intentId, $revision->subjectId, $revision->window, RuntimeReadFixture::observedAt())->status,
        );
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadAvailability::Available, $runtime->diagnostics()->availability);
        self::assertSame('reservation-lifecycle.availability-owner-source-runtime-read', $runtime->diagnostics()->runtimeReadId);
        self::assertSame('reservation-availability-owner-source-runtime-read-v1', $runtime->diagnostics()->version);
    }

    public function test_exception_is_dependency_unavailable_without_leak(): void
    {
        $revision = RuntimeReadFixture::revision();
        $runtime = $this->runtime(new ThrowingRuntimeReadSourceStub);

        self::assertSame(
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::DependencyUnavailable,
            $runtime->read($revision->intentId, $revision->subjectId, $revision->window, RuntimeReadFixture::observedAt())->status,
        );
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadAvailability::DependencyUnavailable, $runtime->diagnostics()->availability);
    }

    /** @return iterable<string, array{ReservationAvailabilityReadResult, ReservationAvailabilityOwnerSourceRuntimeReadStatus}> */
    public static function reductionCases(): iterable
    {
        $revision = RuntimeReadFixture::revision();

        yield 'available' => [ReservationAvailabilityReadResult::available($revision), ReservationAvailabilityOwnerSourceRuntimeReadStatus::Allowed];
        yield 'conflicting' => [ReservationAvailabilityReadResult::conflicting($revision), ReservationAvailabilityOwnerSourceRuntimeReadStatus::Conflicting];
        yield 'missing' => [ReservationAvailabilityReadResult::missing($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeReadStatus::Missing];
        yield 'corrupted' => [ReservationAvailabilityReadResult::corrupted($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeReadStatus::Corrupted];
        yield 'unavailable' => [ReservationAvailabilityReadResult::dependencyUnavailable($revision->intentId), ReservationAvailabilityOwnerSourceRuntimeReadStatus::DependencyUnavailable];
    }

    private function runtime(ReservationAvailabilityOwnerSource $source): DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1
    {
        return new DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1(
            $source,
            new DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy,
        );
    }
}

final readonly class RuntimeReadSourceStub implements ReservationAvailabilityOwnerSource
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

final readonly class ThrowingRuntimeReadSourceStub implements ReservationAvailabilityOwnerSource
{
    public function append(ReservationAvailabilityRevisionState $revision): ReservationAvailabilityWriteResult
    {
        throw new RuntimeException('secret database diagnostic');
    }

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityReadResult {
        throw new RuntimeException('secret database diagnostic');
    }

    public function history(ReservationAvailabilityIntentId $intentId): array
    {
        throw new RuntimeException('secret database diagnostic');
    }
}

final class RuntimeReadFixture
{
    public static function revision(): ReservationAvailabilityRevisionState
    {
        $effectiveAt = new DateTimeImmutable('2026-08-01T08:00:00Z');

        return new ReservationAvailabilityRevisionState(
            ReservationAvailabilityIntentId::fromString('00000000-0000-4000-8000-000000005415'),
            1,
            ReservationAvailabilitySubjectId::fromString('00000000-0000-4000-8000-000000005416'),
            new ReservationAvailabilityWindow(
                new DateTimeImmutable('2026-08-02T08:00:00Z'),
                new DateTimeImmutable('2026-08-02T09:00:00Z'),
            ),
            ReservationAvailabilityRevisionDecision::Proposed,
            $effectiveAt,
            $effectiveAt,
        );
    }

    public static function observedAt(): ReservationAvailabilityObservedAt
    {
        return new ReservationAvailabilityObservedAt(new DateTimeImmutable('2026-08-03T08:00:00Z'));
    }
}
