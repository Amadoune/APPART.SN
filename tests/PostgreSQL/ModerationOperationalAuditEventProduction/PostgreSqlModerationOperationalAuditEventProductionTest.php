<?php

namespace Tests\PostgreSQL\ModerationOperationalAuditEventProduction;

use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationOperationalAuditEventProduction\Contract\ModerationOperationalAuditOutboxAppenderV1;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;
use App\Application\ModerationOperationalAuditEventProduction\OperationalAuditModerationCaseOrchestratorV1;
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
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOperationalAuditOutboxAppender;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Application\OperationalAuditEventContract\FindingRecordedEventV1;
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

final class PostgreSqlModerationOperationalAuditEventProductionTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationCaseStore $cases;

    private PostgreSqlModerationQueueStore $queue;

    private DeterministicModerationRuntimeV1 $runtime;

    private DeterministicModerationCaseOrchestratorV1 $inner;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $this->runtime = new DeterministicModerationRuntimeV1(
            $this->cases,
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            new DeterministicModerationQueueRuntimeV1($this->queue),
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        );
        $this->inner = new DeterministicModerationCaseOrchestratorV1($this->runtime);
    }

    #[Test]
    public function four_applied_paths_store_exactly_four_messages_with_the_certified_routes(): void
    {
        $orchestrator = $this->orchestrator(
            new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection),
        );
        $submit = new SubmitModerationReportV1(
            $this->id(1), $this->id(2), $this->id(3), 'Listing', $this->id(4),
            'fraud', 'opaque-statement', $this->now(), 'v1',
        );
        $created = $orchestrator->submit($submit);
        self::assertSame(ModerationCommandStatus::Applied, $created->status);
        self::assertNotNull($created->caseId);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $orchestrator->submit($submit)->status);
        self::assertSame(1, $this->tableCount('moderation_reports.outbox_messages'));
        self::assertSame(ModerationCommandStatus::ForbiddenActor, $orchestrator->validate(
            new ValidateModerationReportV1(
                $this->id(40), $created->caseId, $submit->reportId, $submit->actorAccountId,
                'Accepted', 'invalid-actor', 1, $this->now(), 'v1',
            ),
        )->status);
        self::assertSame(1, $this->tableCount('moderation_reports.outbox_messages'));

        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->validate(
            new ValidateModerationReportV1(
                $this->id(5), $created->caseId, $submit->reportId, $this->id(6),
                'Accepted', 'valid', 1, $this->now(), 'v1',
            ),
        )->status);
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->recordFinding(
            new RecordModerationFindingV1(
                $this->id(7), $created->caseId, $this->id(8), $this->id(9),
                [$submit->reportId], 'confirmed', ['opaque-proof'], 2, $this->now(), 'v1',
            ),
        )->status);

        $item = new ModerationQueueItemState(
            $this->id(10), $created->caseId, 80, 'fraud', 'Available',
            null, null, null, 3, $this->now(),
        );
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->claim(
            new ClaimModerationQueueItemV1(
                $this->id(11), $item->queueItemId, $this->id(12), $this->id(13),
                $this->now()->modify('+5 minutes'), $this->now(), 'v1',
            ),
        )->status);

        self::assertSame(4, $this->tableCount('moderation_reports.outbox_messages'));
        self::assertSame(8, $this->tableCount('moderation_reports.outbox_deliveries'));
        self::assertSame(
            [
                'moderation.delivery-observation',
                'moderation.delivery-observation',
                'moderation.delivery-observation',
                'moderation.delivery-observation',
                'moderation.queue',
                'moderation.queue',
                'moderation.timeline',
                'moderation.timeline',
            ],
            $this->connection->query(
                'SELECT destination FROM moderation_reports.outbox_deliveries ORDER BY destination',
            )->fetchAll(PDO::FETCH_COLUMN),
        );
        self::assertSame(
            [
                'moderation.finding.recorded.v1',
                'moderation.queue-item.claimed.v1',
                'moderation.report.submitted.v1',
                'moderation.report.validated.v1',
            ],
            $this->connection->query(
                'SELECT event_type FROM moderation_reports.outbox_messages ORDER BY event_type',
            )->fetchAll(PDO::FETCH_COLUMN),
        );
    }

    #[Test]
    public function decision_and_close_are_atomic_and_replay_does_not_append_again(): void
    {
        $orchestrator = $this->orchestrator(
            new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection),
        );
        $submit = new SubmitModerationReportV1(
            $this->id(50), $this->id(51), $this->id(52), 'Listing', $this->id(53),
            'fraud', 'opaque-statement', $this->now(), 'v1',
        );
        $created = $orchestrator->submit($submit);
        self::assertNotNull($created->caseId);
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->validate(
            new ValidateModerationReportV1(
                $this->id(54), $created->caseId, $submit->reportId, $this->id(55),
                'Accepted', 'valid', 1, $this->now(), 'v1',
            ),
        )->status);
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->recordFinding(
            new RecordModerationFindingV1(
                $this->id(56), $created->caseId, $this->id(57), $this->id(58),
                [$submit->reportId], 'confirmed', ['opaque-proof'], 2, $this->now(), 'v1',
            ),
        )->status);

        $decision = new IssueModerationDecisionV1(
            $this->id(59), $created->caseId, $this->id(60), $this->id(61),
            [$this->id(57)], 'Confirmed', 'None', null, 3, $this->now(), 'v1',
        );
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->issueDecision($decision)->status);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $orchestrator->issueDecision($decision)->status);

        $close = new CloseModerationCaseV1(
            $this->id(62), $created->caseId, $this->id(63), 'resolved', 4, $this->now(), 'v1',
        );
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->close($close)->status);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $orchestrator->close($close)->status);

        self::assertSame(5, $this->tableCount('moderation_reports.outbox_messages'));
        self::assertSame(
            [
                'moderation.case.closed.v1',
                'moderation.decision.issued.v1',
                'moderation.finding.recorded.v1',
                'moderation.report.submitted.v1',
                'moderation.report.validated.v1',
            ],
            $this->connection->query(
                'SELECT event_type FROM moderation_reports.outbox_messages ORDER BY event_type',
            )->fetchAll(PDO::FETCH_COLUMN),
        );
        self::assertSame(
            ['closureCode' => 'resolved', 'currentDecisionId' => $this->id(60)],
            json_decode((string) $this->connection->query(
                "SELECT canonical_transport->'payload'->'payload'
                 FROM moderation_reports.outbox_messages
                 WHERE event_type='moderation.case.closed.v1'",
            )->fetchColumn(), true, flags: JSON_THROW_ON_ERROR),
        );
        self::assertSame(
            [
                'decisionId' => $this->id(60),
                'disposition' => 'Confirmed',
                'targetAction' => 'None',
                'supersededDecisionId' => null,
            ],
            json_decode((string) $this->connection->query(
                "SELECT canonical_transport->'payload'->'payload'
                 FROM moderation_reports.outbox_messages
                 WHERE event_type='moderation.decision.issued.v1'",
            )->fetchColumn(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function historical_outbox_rejection_rolls_back_decision_mutation(): void
    {
        $orchestrator = $this->orchestrator(
            new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection),
        );
        $submit = new SubmitModerationReportV1(
            $this->id(80), $this->id(81), $this->id(82), 'Listing', $this->id(83),
            'fraud', 'opaque-statement', $this->now(), 'v1',
        );
        $created = $orchestrator->submit($submit);
        self::assertNotNull($created->caseId);
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->validate(
            new ValidateModerationReportV1(
                $this->id(84), $created->caseId, $submit->reportId, $this->id(85),
                'Accepted', 'valid', 1, $this->now(), 'v1',
            ),
        )->status);
        self::assertSame(ModerationCommandStatus::Applied, $orchestrator->recordFinding(
            new RecordModerationFindingV1(
                $this->id(86), $created->caseId, $this->id(87), $this->id(88),
                [$submit->reportId], 'confirmed', ['opaque-proof'], 2, $this->now(), 'v1',
            ),
        )->status);

        $rejected = new class implements ModerationOutboxAppenderV1
        {
            public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult
            {
                return ModerationOutboxAppendResult::Rejected;
            }
        };
        $orchestrator = $this->orchestrator(
            new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection),
            $rejected,
        );
        $result = $orchestrator->issueDecision(new IssueModerationDecisionV1(
            $this->id(89), $created->caseId, $this->id(90), $this->id(91),
            [$this->id(87)], 'Confirmed', 'None', null, 3, $this->now(), 'v1',
        ));

        self::assertSame(ModerationCommandStatus::Rejected, $result->status);
        self::assertNull($this->cases->read($created->caseId)->state?->currentDecisionId);
        self::assertSame(3, $this->tableCount('moderation_reports.outbox_messages'));
    }

    #[Test]
    public function outbox_rejection_rolls_back_the_applied_business_mutation(): void
    {
        $rejected = new class implements ModerationOperationalAuditOutboxAppenderV1
        {
            public function append(
                ModerationOperationalAuditOutboxMessageV1 $message,
            ): ModerationOutboxAppendResult {
                return ModerationOutboxAppendResult::Rejected;
            }
        };
        $result = $this->orchestrator($rejected)->submit(new SubmitModerationReportV1(
            $this->id(20), $this->id(21), $this->id(22), 'Listing', $this->id(23),
            'fraud', 'opaque', $this->now(), 'v1',
        ));

        self::assertSame(ModerationCommandStatus::Rejected, $result->status);
        self::assertSame(0, $this->tableCount('moderation_reports.cases'));
        self::assertSame(0, $this->tableCount('moderation_reports.outbox_messages'));
    }

    #[Test]
    public function same_event_identity_with_a_different_checksum_is_fail_closed(): void
    {
        $appender = new PostgreSqlModerationOperationalAuditOutboxAppender($this->connection);
        $first = new ModerationOperationalAuditOutboxMessageV1(new FindingRecordedEventV1(
            $this->id(30), $this->id(31), 1, 'v1', $this->now(), $this->now(),
            $this->id(32), $this->id(33),
        ));
        $divergent = new ModerationOperationalAuditOutboxMessageV1(new FindingRecordedEventV1(
            $this->id(30), $this->id(34), 1, 'v1', $this->now(), $this->now(),
            $this->id(32), $this->id(33),
        ));

        self::assertSame(ModerationOutboxAppendResult::Stored, $appender->append($first));
        self::assertSame(
            ModerationOutboxAppendResult::DivergentMessage,
            $appender->append($divergent),
        );
        self::assertSame(1, $this->tableCount('moderation_reports.outbox_messages'));
    }

    #[Test]
    public function concurrent_identical_appends_converge_without_a_duplicate_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-audit-event-'.bin2hex(random_bytes(8));
        $processes = [];
        for ($worker = 1; $worker <= 2; $worker++) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start operational audit Event worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        self::assertFileExists($barrier.'.ready.1');
        self::assertFileExists($barrier.'.ready.2');
        touch($barrier.'.start');
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $results[] = trim(stream_get_contents($pipes[1]));
            $error = trim(stream_get_contents($pipes[2]));
            self::assertSame(0, proc_close($process), $error);
        }
        sort($results);
        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->tableCount('moderation_reports.outbox_messages'));
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
    }

    private function orchestrator(
        ModerationOperationalAuditOutboxAppenderV1 $outbox,
        ?ModerationOutboxAppenderV1 $historicalOutbox = null,
    ): OperationalAuditModerationCaseOrchestratorV1 {
        return new OperationalAuditModerationCaseOrchestratorV1(
            $this->inner,
            $this->runtime,
            new PostgreSqlModerationAtomicOperation($this->connection),
            $outbox,
            $historicalOutbox ?? new PostgreSqlModerationOutbox(
                $this->connection,
                new ModerationEventTransportSerializer,
                new DeterministicModerationEventRouter,
            ),
        );
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
