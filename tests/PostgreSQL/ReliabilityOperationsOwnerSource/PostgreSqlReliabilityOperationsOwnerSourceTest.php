<?php

namespace Tests\PostgreSQL\ReliabilityOperationsOwnerSource;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsRevisionState;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsWriteResult;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\PostgreSql\PostgreSqlReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Infrastructure\Persistence\ReliabilityOperationsOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReliabilityOperationsOwnerSourceTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlReliabilityOperationsOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        $outboxRollback = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Outbox/Migrations/089_reliability_operations_outbox.down.sql');
        $this->connection->exec($outboxRollback);
        $rollback = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/088_reliability_operations_owner_source.down.sql');
        $this->connection->exec($rollback);
        $sql = (string) file_get_contents(dirname(__DIR__, 3).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/088_reliability_operations_owner_source.sql');
        $this->connection->exec($sql);
        $this->connection->exec('TRUNCATE reliability_operations.owner_current_index, reliability_operations.owner_revision_journal');
        $this->source = new PostgreSqlReliabilityOperationsOwnerSource($this->connection, new ReliabilityOperationsOwnerSourceMapper);
    }

    public function test_seven_streams_are_independent_idempotent_and_temporal(): void
    {
        $scope = new ReliabilityOperationsScopeKey('platform:primary');
        foreach (self::streams() as [$stream, $decision]) {
            $state = self::state($stream, $decision, 1, '10:00:00', '10:00:01');
            self::assertSame(ReliabilityOperationsWriteResult::Applied, $this->source->append($state));
            self::assertSame(ReliabilityOperationsWriteResult::AlreadyApplied, $this->source->append($state));
            $read = $this->source->read($scope, $stream, new ReliabilityOperationsObservedAt(self::at('11:00:00')));
            self::assertSame(ReliabilityOperationsReadStatus::Found, $read->status);
            self::assertSame($decision, $read->revision?->decision);
        }
    }

    public function test_temporal_read_and_optimistic_locking_are_deterministic(): void
    {
        $stream = ReliabilityOperationsStream::Observability;
        self::assertSame(ReliabilityOperationsWriteResult::Applied, $this->source->append(self::state($stream, 'available', 1, '10:00:00', '10:00:01')));
        self::assertSame(ReliabilityOperationsWriteResult::VersionConflict, $this->source->append(self::state($stream, 'degraded', 3, '10:20:00', '10:20:01')));
        self::assertSame(ReliabilityOperationsWriteResult::Applied, $this->source->append(self::state($stream, 'degraded', 2, '10:10:00', '10:10:01')));
        $scope = new ReliabilityOperationsScopeKey('platform:primary');
        self::assertSame('available', $this->source->read($scope, $stream, new ReliabilityOperationsObservedAt(self::at('10:05:00')))->revision?->decision);
        self::assertSame('degraded', $this->source->read($scope, $stream, new ReliabilityOperationsObservedAt(self::at('11:00:00')))->revision?->decision);
    }

    public function test_external_rollback_is_preserved(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(ReliabilityOperationsWriteResult::Applied, $this->source->append(self::state(ReliabilityOperationsStream::Continuity, 'ready', 1, '10:00:00', '10:00:01')));
        self::assertTrue($this->connection->inTransaction());
        $this->connection->rollBack();
        $read = $this->source->read(new ReliabilityOperationsScopeKey('platform:primary'), ReliabilityOperationsStream::Continuity, new ReliabilityOperationsObservedAt(self::at('11:00:00')));
        self::assertSame(ReliabilityOperationsReadStatus::Missing, $read->status);
    }

    public function test_database_rejects_a_decision_outside_its_stream_catalogue(): void
    {
        $this->expectException(PDOException::class);
        $this->connection->exec("INSERT INTO reliability_operations.owner_revision_journal(scope_key,stream_type,revision,decision,effective_at,recorded_at,revision_checksum) VALUES ('platform:invalid','observability',1,'ready','2026-08-07T10:00:00Z','2026-08-07T10:00:01Z','".str_repeat('0', 64)."')");
    }

    /** @return list<array{ReliabilityOperationsStream,string}> */
    private static function streams(): array
    {
        return [
            [ReliabilityOperationsStream::Observability, 'available'],
            [ReliabilityOperationsStream::ServiceHealth, 'healthy'],
            [ReliabilityOperationsStream::Alerting, 'ready'],
            [ReliabilityOperationsStream::Continuity, 'ready'],
            [ReliabilityOperationsStream::MaintenanceOperations, 'ready'],
            [ReliabilityOperationsStream::CapacityPlanning, 'sufficient'],
            [ReliabilityOperationsStream::OperationalReadiness, 'ready'],
        ];
    }

    private static function state(ReliabilityOperationsStream $stream, string $decision, int $revision, string $effective, string $recorded): ReliabilityOperationsRevisionState
    {
        return new ReliabilityOperationsRevisionState('platform:primary', $stream, $revision, $decision, self::at($effective), self::at($recorded));
    }

    private static function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T'.$time.'.123456Z');
    }
}
