<?php

namespace Tests\PostgreSQL\ModerationQueueIdempotence;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationQueueIdempotenceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationQueueStore $queue;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $this->queue = new PostgreSqlModerationQueueStore($this->connection, new ModerationPersistenceMapper);
    }

    #[Test]
    public function migration_is_additive_owner_local_and_reversible(): void
    {
        self::assertTrue($this->tableExists());
        $this->connection->exec((string) file_get_contents($this->migration('.down')));
        self::assertFalse($this->tableExists());
        $this->connection->exec((string) file_get_contents($this->migration()));
        self::assertTrue($this->tableExists());
    }

    #[Test]
    public function exact_replay_and_divergent_intent_are_durable(): void
    {
        $item = $this->item(1);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        $checksum = hash('sha256', 'claim-v1');

        self::assertSame(ModerationQueueClaimResult::Applied, $this->claim($item, 10, 20, $this->id(30), $checksum));
        self::assertSame(ModerationQueueClaimResult::AlreadyApplied, $this->claim($item, 10, 20, $this->id(30), $checksum));
        self::assertSame(ModerationQueueClaimResult::DivergentIntent, $this->claim(
            $item,
            10,
            20,
            $this->id(30),
            hash('sha256', 'claim-v1-divergent'),
        ));
        self::assertSame(1, (int) $this->connection->query(
            'SELECT count(*) FROM moderation_reports.queue_claim_intents',
        )->fetchColumn());
    }

    #[Test]
    public function claim_and_intent_share_the_outer_transaction_and_rollback(): void
    {
        $item = $this->item(2);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        $this->connection->beginTransaction();
        self::assertSame(ModerationQueueClaimResult::Applied, $this->claim(
            $item,
            11,
            21,
            $this->id(31),
            hash('sha256', 'rollback'),
        ));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();

        self::assertSame('Available', $this->queue->read($item->queueItemId)?->state);
        self::assertSame(0, (int) $this->connection->query(
            'SELECT count(*) FROM moderation_reports.queue_claim_intents',
        )->fetchColumn());
    }

    #[Test]
    public function two_concurrent_intents_converge_under_the_owner_advisory_lock(): void
    {
        $item = $this->item(3);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $this->queue->project($item));
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'moderation-queue-intent-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $worker) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $worker, $item->queueItemId],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start moderation Queue worker.');
            }
            $processes[] = [$process, $pipes];
        }
        $deadline = microtime(true) + 10;
        while ((! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) && microtime(true) < $deadline) {
            usleep(1000);
        }
        if (! is_file($barrier.'.ready.1') || ! is_file($barrier.'.ready.2')) {
            throw new RuntimeException('Moderation Queue workers did not reach the barrier.');
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
        self::assertSame(['applied', 'lease_conflict'], $results);
        self::assertSame(1, (int) $this->connection->query(
            'SELECT count(*) FROM moderation_reports.queue_claim_intents',
        )->fetchColumn());
    }

    private function claim(
        ModerationQueueItemState $item,
        int $lease,
        int $owner,
        string $intent,
        string $checksum,
    ): ModerationQueueClaimResult {
        return $this->queue->claim(
            $item->queueItemId,
            $this->id($lease),
            $this->id($owner),
            new DateTimeImmutable('2026-07-30T12:05:00+00:00'),
            $this->time(),
            $intent,
            $checksum,
        );
    }

    private function item(int $suffix): ModerationQueueItemState
    {
        return new ModerationQueueItemState(
            $this->id($suffix),
            $this->id(100 + $suffix),
            80,
            'fraud',
            'Available',
            null,
            null,
            null,
            1,
            $this->time(),
        );
    }

    private function tableExists(): bool
    {
        return (bool) $this->connection->query(
            "SELECT to_regclass('moderation_reports.queue_claim_intents') IS NOT NULL",
        )->fetchColumn();
    }

    private function migration(string $suffix = ''): string
    {
        return dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/064_moderation_queue_claim_intents'.$suffix.'.sql';
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
