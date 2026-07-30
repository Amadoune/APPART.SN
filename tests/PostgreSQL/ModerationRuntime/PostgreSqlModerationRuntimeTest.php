<?php

namespace Tests\PostgreSQL\ModerationRuntime;

use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    #[Test]
    public function runtime_queue_projects_claims_reclaims_and_checkpoints_owner_locally(): void
    {
        $mapper = new ModerationPersistenceMapper;
        $store = new PostgreSqlModerationQueueStore($this->connection, $mapper);
        $queue = new DeterministicModerationQueueRuntimeV1($store);
        $runtime = new DeterministicModerationRuntimeV1(
            new PostgreSqlModerationCaseStore($this->connection, $mapper),
            new PostgreSqlModerationDecisionStore($this->connection, $mapper),
            $queue,
            new DeterministicModerationRuntimeAvailabilityPolicy([
                'case_store' => true,
                'decision_store' => true,
                'queue_store' => true,
            ]),
        );
        $now = new DateTimeImmutable('2026-07-29T13:00:00+00:00');
        $item = new ModerationQueueItemState(
            $this->id(1),
            $this->id(2),
            80,
            'fraud',
            'Available',
            null,
            null,
            null,
            1,
            $now,
        );

        self::assertSame(ModerationRuntimeStatus::Healthy, $runtime->inspect()->status);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $runtime->queue()->project($item));
        self::assertSame(ModerationQueueClaimResult::Claimed, $runtime->queue()->claim(
            $item->queueItemId,
            $this->id(3),
            $this->id(4),
            new DateTimeImmutable('2026-07-29T13:01:00+00:00'),
            $now,
        ));
        self::assertSame(ModerationQueueClaimResult::Claimed, $runtime->queue()->claim(
            $item->queueItemId,
            $this->id(5),
            $this->id(6),
            new DateTimeImmutable('2026-07-29T13:03:00+00:00'),
            new DateTimeImmutable('2026-07-29T13:02:00+00:00'),
        ));
        self::assertSame($this->id(5), $runtime->queue()->read($item->queueItemId)?->leaseId);
        self::assertSame(ModerationPersistenceWriteResult::Applied, $runtime->queue()->checkpoint('moderation-queue-v1', 10, $now));
        self::assertSame(ModerationPersistenceWriteResult::AlreadyApplied, $runtime->queue()->checkpoint('moderation-queue-v1', 10, $now));
    }

    private function id(int $suffix): string
    {
        return sprintf('53100000-0000-4000-8000-%012d', $suffix);
    }
}
