<?php

namespace Tests\Unit\ModerationRuntime;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeDiagnosticCode;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationRuntimeTest extends TestCase
{
    #[Test]
    public function runtime_exposes_only_the_three_owner_components(): void
    {
        $cases = $this->createMock(ModerationCaseStore::class);
        $decisions = $this->createMock(ModerationDecisionStore::class);
        $queueStore = $this->createMock(ModerationQueueStore::class);
        $queue = new DeterministicModerationQueueRuntimeV1($queueStore);
        $availability = new DeterministicModerationRuntimeAvailabilityPolicy([
            'case_store' => true,
            'decision_store' => true,
            'queue_store' => true,
        ]);
        $runtime = new DeterministicModerationRuntimeV1($cases, $decisions, $queue, $availability);

        self::assertSame($cases, $runtime->cases());
        self::assertSame($decisions, $runtime->decisions());
        self::assertSame($queue, $runtime->queue());
        self::assertSame(ModerationRuntimeStatus::Healthy, $runtime->inspect()->status);
        self::assertNull($runtime->inspect()->diagnostic);
    }

    #[Test]
    public function availability_is_deterministic_and_fail_closed(): void
    {
        $policy = new DeterministicModerationRuntimeAvailabilityPolicy([
            'case_store' => true,
            'decision_store' => false,
            'queue_store' => true,
        ]);

        self::assertSame(ModerationRuntimeStatus::Unavailable, $policy->inspect()->status);
        self::assertSame(ModerationRuntimeDiagnosticCode::DecisionStoreMissing, $policy->inspect()->diagnostic);

        $missing = new DeterministicModerationRuntimeAvailabilityPolicy([]);
        self::assertSame(ModerationRuntimeStatus::Unavailable, $missing->inspect()->status);
        self::assertSame(ModerationRuntimeDiagnosticCode::CaseStoreMissing, $missing->inspect()->diagnostic);
    }

    #[Test]
    public function queue_runtime_is_the_closed_owner_facade(): void
    {
        $queue = new DeterministicModerationQueueRuntimeV1($this->createMock(ModerationQueueStore::class));

        self::assertInstanceOf(ModerationQueueRuntimeV1::class, $queue);
    }
}
