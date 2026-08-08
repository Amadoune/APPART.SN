<?php

namespace Tests\PostgreSQL\ReservationAvailabilityOwnerReader;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerReader\OwnerReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityResultV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityStatusV1;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\ReservationAvailabilityOwnerSource\ReservationAvailabilityOwnerSourceMapperTest;

final class PostgreSqlReservationAvailabilityOwnerReaderTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlReservationAvailabilityOwnerSource $source;

    private OwnerReservationAvailabilityReaderV1 $reader;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE reservation_lifecycle.availability_intent_revisions');
        $this->source = new PostgreSqlReservationAvailabilityOwnerSource($this->connection, new ReservationAvailabilityOwnerSourceMapper);
        $this->reader = new OwnerReservationAvailabilityReaderV1(
            new DeterministicReservationAvailabilityOwnerSourceRuntimeReadV1(
                $this->source,
                new DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy,
            ),
        );
    }

    public function test_reader_propagates_all_runtime_read_results_mechanically(): void
    {
        self::assertSame(ReservationAvailabilityStatusV1::Missing, $this->read(1)->status);

        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00'));
        self::assertSame(ReservationAvailabilityStatusV1::Available, $this->read(1)->status);

        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(2, ReservationAvailabilityRevisionDecision::Held, '09:00'));
        $this->source->append(ReservationAvailabilityOwnerSourceMapperTest::state(1, ReservationAvailabilityRevisionDecision::Proposed, '09:01', 2));
        self::assertSame(ReservationAvailabilityStatusV1::Conflicting, $this->read(2)->status);

        $statement = $this->connection->prepare("UPDATE reservation_lifecycle.availability_intent_revisions SET revision_checksum=repeat('0',64) WHERE availability_intent_id=:intent_id");
        $statement->execute(['intent_id' => ReservationAvailabilityOwnerSourceMapperTest::intent()->value]);
        self::assertSame(ReservationAvailabilityStatusV1::Corrupted, $this->read(1)->status);

        $this->connection->exec('DROP TABLE reservation_lifecycle.availability_intent_revisions');
        self::assertSame(ReservationAvailabilityStatusV1::DependencyUnavailable, $this->read(1)->status);
    }

    private function read(int $intentSuffix): ReservationAvailabilityResultV1
    {
        return $this->reader->read(
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
