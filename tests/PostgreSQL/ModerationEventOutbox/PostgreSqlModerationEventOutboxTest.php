<?php

namespace Tests\PostgreSQL\ModerationEventOutbox;

use App\Application\ModerationAtomicOperation\ModerationAtomicDecision;
use App\Application\ModerationAtomicOperation\ModerationAtomicMutation;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\SubmitModerationReportV1;
use App\Application\ModerationOrchestration\DeterministicModerationCaseOrchestratorV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
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

final class PostgreSqlModerationEventOutboxTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationOutbox $outbox;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->outbox = new PostgreSqlModerationOutbox(
            $this->connection,
            new ModerationEventTransportSerializer,
            new DeterministicModerationEventRouter,
        );
    }

    #[Test]
    public function append_is_immutable_idempotent_and_divergence_safe(): void
    {
        $message = $this->message();
        self::assertSame(ModerationOutboxAppendResult::Stored, $this->outbox->append($message));
        self::assertSame(ModerationOutboxAppendResult::AlreadyStored, $this->outbox->append($message));
        self::assertSame(
            ModerationOutboxAppendResult::DivergentMessage,
            $this->outbox->append($this->message('2026-07-30T12:00:01+00:00')),
        );
        self::assertSame(1, $this->tableCount('outbox_messages'));
        self::assertSame(3, $this->tableCount('outbox_deliveries'));
    }

    #[Test]
    public function claim_release_retry_delivery_replay_and_quarantine_are_deterministic(): void
    {
        $message = $this->message();
        $this->outbox->append($message);
        $now = new DateTimeImmutable('2026-07-30T12:01:00+00:00');

        $first = $this->outbox->claimNext('worker-a', $now);
        self::assertNotNull($first);
        self::assertSame(1, $first->attempt);
        self::assertTrue($this->outbox->release($first, $now));
        $released = $this->outbox->claimNext('worker-b', $now);
        self::assertNotNull($released);
        self::assertTrue($this->outbox->retry($released, $now->modify('+1 second'), 'temporary'));

        $retried = $this->outbox->claimNext('worker-c', $now->modify('+2 seconds'));
        self::assertNotNull($retried);
        self::assertTrue($this->outbox->markDelivered($retried, $now->modify('+3 seconds')));
        self::assertTrue($this->outbox->replay(
            $retried->message->messageId,
            $retried->destination->value,
            $now->modify('+4 seconds'),
        ));
        $replayed = $this->outbox->claimNext('worker-d', $now->modify('+5 seconds'));
        self::assertNotNull($replayed);
        self::assertTrue($this->outbox->quarantine($replayed, 'terminal'));
        self::assertTrue($this->outbox->replay(
            $replayed->message->messageId,
            $replayed->destination->value,
            $now->modify('+6 seconds'),
        ));
    }

    #[Test]
    public function business_mutation_and_outbox_append_share_the_certified_atomic_boundary(): void
    {
        $mapper = new ModerationPersistenceMapper;
        $cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $orchestrator = new DeterministicModerationCaseOrchestratorV1(new DeterministicModerationRuntimeV1(
            $cases,
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            new DeterministicModerationQueueRuntimeV1($queue),
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        ));
        $atomic = new ModerationAtomicMutation(
            new PostgreSqlModerationAtomicOperation($this->connection),
            $this->outbox,
        );
        $command = new SubmitModerationReportV1(
            $this->id(10),
            $this->id(11),
            $this->id(12),
            'Listing',
            $this->id(13),
            'fraud',
            'opaque',
            $this->time(),
            'v1',
        );

        $this->connection->beginTransaction();
        $result = $atomic->execute(
            static fn (): ModerationCommandResult => $orchestrator->submit($command),
            $this->message(),
        );
        self::assertSame(ModerationAtomicDecision::Commit, $result->decision);
        self::assertSame(1, $this->tableCount('outbox_messages'));
        self::assertNotNull($result->value->caseId);
        $caseId = $result->value->caseId;
        $this->connection->rollBack();

        self::assertSame(0, $this->tableCount('outbox_messages'));
        self::assertSame(ModerationPersistenceReadStatus::Missing, $cases->read($caseId)->status);
    }

    #[Test]
    public function divergent_outbox_append_rolls_back_the_business_mutation(): void
    {
        self::assertSame(ModerationOutboxAppendResult::Stored, $this->outbox->append($this->message()));
        $mapper = new ModerationPersistenceMapper;
        $cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $queue = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $orchestrator = new DeterministicModerationCaseOrchestratorV1(new DeterministicModerationRuntimeV1(
            $cases,
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            new DeterministicModerationQueueRuntimeV1($queue),
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        ));
        $atomic = new ModerationAtomicMutation(
            new PostgreSqlModerationAtomicOperation($this->connection),
            $this->outbox,
        );
        $command = new SubmitModerationReportV1(
            $this->id(20),
            $this->id(21),
            $this->id(22),
            'Listing',
            $this->id(23),
            'fraud',
            'opaque',
            $this->time(),
            'v1',
        );
        $mutated = null;
        $result = $atomic->execute(function () use ($orchestrator, $command, &$mutated): ModerationCommandResult {
            return $mutated = $orchestrator->submit($command);
        }, $this->message('2026-07-30T12:00:01+00:00'));

        self::assertSame(ModerationAtomicDecision::Rollback, $result->decision);
        self::assertSame(ModerationOutboxAppendResult::DivergentMessage, $result->value);
        self::assertInstanceOf(ModerationCommandResult::class, $mutated);
        self::assertNotNull($mutated->caseId);
        self::assertSame(ModerationPersistenceReadStatus::Missing, $cases->read($mutated->caseId)->status);
        self::assertSame(1, $this->tableCount('outbox_messages'));
    }

    #[Test]
    public function concurrent_identical_appends_converge_to_one_stored_and_one_already_stored(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-outbox-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Moderation Outbox worker.');
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
        self::assertSame(1, $this->tableCount('outbox_messages'));
    }

    private function message(string $recordedAt = '2026-07-30T12:00:00+00:00'): ModerationDeliveryMessageV1
    {
        return new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::ReportSubmitted,
            $this->id(1),
            1,
            ['reportId' => $this->id(2)],
            'v1',
            $this->time(),
            new DateTimeImmutable($recordedAt),
            $this->id(3),
            $this->id(4),
        ));
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query(
            "SELECT count(*) FROM moderation_reports.{$table}",
        )->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53b10000-0000-4000-8000-%012d', $suffix);
    }

    private function time(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
