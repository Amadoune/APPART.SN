<?php

namespace Tests\PostgreSQL\ReservationAvailabilityOwnerSourceRuntimeRead;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadAvailability;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ReservationAvailabilityOwnerSource\ReservationAvailabilityOwnerSourceMapperTest;

final class PostgreSqlReservationAvailabilityOwnerSourceRuntimeReadTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlReservationAvailabilityOwnerSource $source;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE reservation_lifecycle.availability_intent_revisions');
        $this->source = new PostgreSqlReservationAvailabilityOwnerSource($this->connection, new ReservationAvailabilityOwnerSourceMapper);
    }

    public function test_runtime_read_covers_allowed_conflicting_missing_corruption_unavailability_and_recovery(): void
    {
        $runtimeRead = $this->runtimeRead();
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Missing, $this->read($runtimeRead, 1)->status);

        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00'));
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Allowed, $this->read($runtimeRead, 1)->status);

        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '09:00'));
        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '09:01', 2));
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Conflicting, $this->read($runtimeRead, 2)->status);

        $statement = $this->connection->prepare("UPDATE reservation_lifecycle.availability_intent_revisions SET revision_checksum=repeat('0',64) WHERE availability_intent_id=:intent_id");
        $statement->execute(['intent_id' => ReservationAvailabilityOwnerSourceMapperTest::intent()->value]);
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Corrupted, $this->read($runtimeRead, 1)->status);

        $this->connection->exec('DROP TABLE reservation_lifecycle.availability_intent_revisions');
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadStatus::DependencyUnavailable, $this->read($runtimeRead, 1)->status);
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadAvailability::DependencyUnavailable, $runtimeRead->diagnostics()->availability);

        $this->migrate();
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeReadAvailability::Available, $runtimeRead->diagnostics()->availability);
    }

    private function runtimeRead(): DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1
    {
        return new DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1(
            $this->source,
            new DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy,
        );
    }

    private function read(DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1 $runtimeRead, int $intentSuffix): ReservationAvailabilityOwnerSourceRuntimeReadResult
    {
        return $runtimeRead->read(
            ReservationAvailabilityOwnerSourceMapperTest::intent($intentSuffix),
            ReservationAvailabilityOwnerSourceMapperTest::subject(),
            ReservationAvailabilityOwnerSourceMapperTest::window(),
            new ReservationAvailabilityObservedAt(new DateTimeImmutable('2026-08-01T09:30:00Z')),
        );
    }

    private function migrate(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'074_reservation_availability_owner_local_source.sql'));
    }
}
