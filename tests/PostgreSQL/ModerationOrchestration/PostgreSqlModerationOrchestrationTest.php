<?php

namespace Tests\PostgreSQL\ModerationOrchestration;

use App\Application\ModerationOrchestration\Contract\ClaimModerationQueueItemV1;
use App\Application\ModerationOrchestration\Contract\CloseModerationCaseV1;
use App\Application\ModerationOrchestration\Contract\IssueModerationDecisionV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\RecordModerationFindingV1;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\Contract\ValidateModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationOrchestrationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationCaseStore $cases;

    private PostgreSqlModerationQueueStore $queue;

    private DeterministicModerationCaseOrchestratorV1 $orchestrator;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $queueRuntime = new DeterministicModerationQueueRuntimeV1($this->queue);
        $this->orchestrator = new DeterministicModerationCaseOrchestratorV1(new DeterministicModerationRuntimeV1(
            $this->cases, new PostgreSqlModerationDecisionStore($this->connection, $mapper), $queueRuntime,
            new DeterministicModerationRuntimeAvailabilityPolicy(['case_store' => true, 'decision_store' => true, 'queue_store' => true]),
        ));
    }

    #[Test]
    public function six_commands_cover_workflow_four_eyes_supersession_and_queue_idempotence(): void
    {
        $submit = $this->submit(10, 100, 1);
        $created = $this->orchestrator->submit($submit);
        self::assertSame(ModerationCommandStatus::Applied, $created->status);
        self::assertNotNull($created->caseId);
        $caseId = $created->caseId;
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $this->orchestrator->submit($submit)->status);
        self::assertSame(ModerationCommandStatus::DivergentIntent, $this->orchestrator->submit(new SubmitModerationReportV1(
            $submit->intentId, $submit->reportId, $submit->actorAccountId, $submit->targetType, $submit->targetId,
            'spam', $submit->statementReference, $submit->occurredAt, $submit->policyVersion,
        ))->status);
        self::assertSame(ModerationCommandStatus::ForbiddenActor, $this->orchestrator->validate($this->validation(11, $caseId, 1, 1))->status);
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->validate($this->validation(12, $caseId, 2, 1))->status);
        self::assertSame(ModerationCommandStatus::ForbiddenActor, $this->orchestrator->recordFinding($this->finding(13, $caseId, 2, 2))->status);
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->recordFinding($this->finding(14, $caseId, 3, 2))->status);
        self::assertSame(ModerationCommandStatus::FourEyesViolation, $this->orchestrator->issueDecision($this->decision(15, $caseId, 300, 1, 3))->status);
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->issueDecision($this->decision(16, $caseId, 300, 4, 3))->status);
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->issueDecision($this->decision(17, $caseId, 301, 5, 4, $this->id(300)))->status);
        $close = new CloseModerationCaseV1($this->id(18), $caseId, $this->id(6), 'resolved', 5, $this->time(), 'v1');
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->close($close)->status);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $this->orchestrator->close($close)->status);
        self::assertSame(ModerationCommandStatus::DivergentIntent, $this->orchestrator->close(new CloseModerationCaseV1(
            $close->intentId, $close->caseId, $close->actorAccountId, 'other', $close->expectedVersion, $close->occurredAt, $close->policyVersion,
        ))->status);
        self::assertSame('Closed', $this->cases->read($caseId)->state?->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM moderation_reports.decision_supersessions')->fetchColumn());

        $item = new ModerationQueueItemState($this->id(400), $caseId, 80, 'fraud', 'Available', null, null, null, 6, $this->time());
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        $claim = new ClaimModerationQueueItemV1($this->id(401), $item->queueItemId, $this->id(7), $this->id(402), new DateTimeImmutable('2026-07-30T12:05:00+00:00'), $this->time(), 'v1');
        self::assertSame(ModerationCommandStatus::Applied, $this->orchestrator->claim($claim)->status);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $this->orchestrator->claim($claim)->status);
        self::assertSame(ModerationCommandStatus::DivergentIntent, $this->orchestrator->claim(new ClaimModerationQueueItemV1(
            $claim->intentId, $claim->queueItemId, $claim->actorAccountId, $this->id(403), $claim->leaseExpiresAt, $claim->occurredAt, $claim->policyVersion,
        ))->status);
    }

    #[Test]
    public function stale_competing_mutations_yield_one_applied_and_one_version_conflict(): void
    {
        $created = $this->orchestrator->submit($this->submit(20, 101, 10));
        self::assertNotNull($created->caseId);
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-orchestration-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker, $created->caseId, $this->id(101)],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start moderation orchestration worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            self::assertSame('', trim(stream_get_contents($pipes[2])));
            self::assertSame(0, proc_close($process));
        }
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        sort($results);
        self::assertSame(['applied', 'version_conflict'], $results);
    }

    #[Test]
    public function savepoint_and_outer_rollback_leave_no_partial_write(): void
    {
        $this->connection->beginTransaction();
        $created = $this->orchestrator->submit($this->submit(30, 102, 20));
        self::assertSame(ModerationCommandStatus::Applied, $created->status);
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        self::assertNotNull($created->caseId);
        self::assertSame(ModerationPersistenceReadStatus::Missing, $this->cases->read($created->caseId)->status);
    }

    private function submit(int $intent, int $report, int $actor): SubmitModerationReportV1
    {
        return new SubmitModerationReportV1($this->id($intent), $this->id($report), $this->id($actor), 'Listing', $this->id(900), 'fraud', 'opaque', $this->time(), 'v1');
    }

    private function validation(int $intent, string $caseId, int $actor, int $version, int $report = 100): ValidateModerationReportV1
    {
        return new ValidateModerationReportV1($this->id($intent), $caseId, $this->id($report), $this->id($actor), 'Accepted', 'verified', $version, $this->time(), 'v1');
    }

    private function finding(int $intent, string $caseId, int $actor, int $version): RecordModerationFindingV1
    {
        return new RecordModerationFindingV1($this->id($intent), $caseId, $this->id(200), $this->id($actor), [$this->id(100)], 'confirmed', ['opaque'], $version, $this->time(), 'v1');
    }

    private function decision(int $intent, string $caseId, int $decision, int $actor, int $version, ?string $superseded = null): IssueModerationDecisionV1
    {
        return new IssueModerationDecisionV1($this->id($intent), $caseId, $this->id($decision), $this->id($actor), [$this->id(200)], 'Confirmed', 'None', $superseded, $version, $this->time(), 'v1');
    }

    private function id(int $suffix): string
    {
        return sprintf('53f10000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
