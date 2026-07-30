<?php

namespace Tests\PostgreSQL\ModerationAtomicOperation;

use App\Application\ModerationAtomicOperation\ModerationAtomicDecision;
use App\Application\ModerationAtomicOperation\ModerationAtomicMutation;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationOutboxAppender;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceReadStatus;
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

final class PostgreSqlModerationAtomicOperationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationCaseStore $cases;

    private DeterministicModerationCaseOrchestratorV1 $orchestrator;

    private ModerationAtomicMutation $atomic;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $this->orchestrator = new DeterministicModerationCaseOrchestratorV1(new DeterministicModerationRuntimeV1(
            $this->cases,
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            new DeterministicModerationQueueRuntimeV1($queue),
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        ));
        $this->atomic = new ModerationAtomicMutation(
            new PostgreSqlModerationAtomicOperation($this->connection),
            new PostgreSqlModerationOutboxAppender($this->connection, new ModerationEventTransportSerializer),
        );
    }

    #[Test]
    public function successful_mutation_and_append_commit_together_and_are_idempotent(): void
    {
        $command = $this->submit(1);
        $message = $this->message(1, ['reportId' => $command->reportId]);
        $first = $this->atomic->execute(fn (): ModerationCommandResult => $this->orchestrator->submit($command), $message);
        $second = $this->atomic->execute(fn (): ModerationCommandResult => $this->orchestrator->submit($command), $message);

        self::assertSame(ModerationAtomicDecision::Commit, $first->decision);
        self::assertSame(ModerationCommandStatus::Applied, $first->value->status);
        self::assertSame(ModerationCommandStatus::AlreadyApplied, $second->value->status);
        self::assertSame(1, $this->countOutbox());
    }

    #[Test]
    public function divergent_append_rolls_back_the_business_mutation(): void
    {
        $original = $this->message(2, ['value' => 'original']);
        $transaction = new PostgreSqlModerationAtomicOperation($this->connection);
        $appender = new PostgreSqlModerationOutboxAppender($this->connection, new ModerationEventTransportSerializer);
        $transaction->execute(fn (): ModerationAtomicWorkResult => ModerationAtomicWorkResult::commit($appender->append($original)));

        $command = $this->submit(2);
        $divergent = $this->message(2, ['value' => 'divergent']);
        $mutated = null;
        $result = $this->atomic->execute(function () use ($command, &$mutated): ModerationCommandResult {
            return $mutated = $this->orchestrator->submit($command);
        }, $divergent);

        self::assertSame(ModerationAtomicDecision::Rollback, $result->decision);
        self::assertSame(ModerationOutboxAppendResult::DivergentMessage, $result->value);
        self::assertInstanceOf(ModerationCommandResult::class, $mutated);
        self::assertNotNull($mutated->caseId);
        self::assertSame(ModerationPersistenceReadStatus::Missing, $this->cases->read($mutated->caseId)->status);
        self::assertSame(1, $this->countOutbox());
    }

    #[Test]
    public function failed_mutation_never_appends(): void
    {
        $result = $this->atomic->execute(
            static fn (): ModerationCommandResult => new ModerationCommandResult(ModerationCommandStatus::VersionConflict),
            $this->message(3, []),
        );

        self::assertSame(ModerationAtomicDecision::Rollback, $result->decision);
        self::assertSame(0, $this->countOutbox());
    }

    #[Test]
    public function enclosing_transaction_uses_a_savepoint_and_outer_rollback_removes_both_writes(): void
    {
        $this->connection->beginTransaction();
        $command = $this->submit(4);
        $result = $this->atomic->execute(
            fn (): ModerationCommandResult => $this->orchestrator->submit($command),
            $this->message(4, []),
        );
        self::assertSame(ModerationAtomicDecision::Commit, $result->decision);
        self::assertTrue($this->connection->inTransaction());
        self::assertSame(1, $this->countOutbox());
        $this->connection->rollBack();

        self::assertSame(0, $this->countOutbox());
        self::assertSame(ModerationPersistenceReadStatus::Missing, $this->cases->read((string) $result->value->caseId)->status);
    }

    #[Test]
    public function concurrent_identical_appends_converge_to_stored_and_already_stored(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-atomic-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Moderation atomic worker.');
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
        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countOutbox());
    }

    private function submit(int $suffix): SubmitModerationReportV1
    {
        return new SubmitModerationReportV1(
            $this->id(100 + $suffix),
            $this->id(200 + $suffix),
            $this->id(300 + $suffix),
            'Listing',
            $this->id(900),
            'fraud',
            'opaque',
            $this->time(),
            'v1',
        );
    }

    /** @param array<string, int|string|null> $payload */
    private function message(int $suffix, array $payload): ModerationDeliveryMessageV1
    {
        return new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::ReportSubmitted,
            $this->id(400 + $suffix),
            1,
            $payload,
            'v1',
            $this->time(),
            $this->time(),
            $this->id(500 + $suffix),
            $this->id(600 + $suffix),
        ));
    }

    private function countOutbox(): int
    {
        return (int) $this->connection->query(
            'SELECT count(*) FROM moderation_reports.atomic_outbox_appends',
        )->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53a10000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
