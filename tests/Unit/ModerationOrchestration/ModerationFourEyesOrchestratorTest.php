<?php

namespace Tests\Unit\ModerationOrchestration;

use App\Application\ModerationOrchestration\Contract\IssueModerationDecisionV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeReport;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceReadResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationFourEyesOrchestratorTest extends TestCase
{
    #[Test]
    public function reporter_cannot_validate_and_involved_actor_cannot_decide(): void
    {
        $state = $this->state();
        $cases = $this->createMock(ModerationCaseStore::class);
        $cases->method('read')->willReturn(ModerationCasePersistenceReadResult::found($state));
        $cases->expects(self::never())->method('save');
        $orchestrator = $this->orchestrator($cases);

        self::assertSame(ModerationCommandStatus::ForbiddenActor, $orchestrator->validate(
            new ValidateModerationReportV1($this->id(20), $state->caseId, $this->id(2), $this->id(3), 'Accepted', 'valid', 1, $this->time(), 'v1'),
        )->status);
        self::assertSame(ModerationCommandStatus::FourEyesViolation, $orchestrator->issueDecision(
            new IssueModerationDecisionV1($this->id(21), $state->caseId, $this->id(7), $this->id(3), [$this->id(6)], 'Confirmed', 'None', null, 1, $this->time(), 'v1'),
        )->status);
    }

    private function orchestrator(ModerationCaseStore $cases): DeterministicModerationCaseOrchestratorV1
    {
        $runtime = $this->createMock(ModerationRuntimeV1::class);
        $runtime->method('inspect')->willReturn(new ModerationRuntimeReport(ModerationRuntimeStatus::Healthy));
        $runtime->method('cases')->willReturn($cases);
        $runtime->method('queue')->willReturn($this->createMock(ModerationQueueRuntimeV1::class));

        return new DeterministicModerationCaseOrchestratorV1($runtime);
    }

    private function state(): ModerationCasePersistenceState
    {
        return new ModerationCasePersistenceState(
            $this->id(1), 'Listing', $this->id(4), 'Open', null, 1, $this->id(10), str_repeat('a', 64), $this->time(),
            [new ModerationPersistenceRecord($this->id(2), ['actorAccountId' => $this->id(3), 'disposition' => 'Accepted', 'validationActorId' => $this->id(5)], $this->time())],
            [new ModerationPersistenceRecord($this->id(6), ['actorAccountId' => $this->id(8), 'reportIds' => [$this->id(2)]], $this->time())],
            [],
        );
    }

    private function id(int $suffix): string
    {
        return sprintf('53f00000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T10:00:00+00:00');
    }
}
