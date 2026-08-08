<?php

namespace Tests\PostgreSQL\ReservationAvailabilityOwnerSourceRuntime;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\DeterministicReservationAvailabilityOwnerSourceRuntimeV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\ReservationAvailabilityOwnerSourceRuntimeAvailability;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationAvailabilityOwnerSource;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReservationAvailabilityOwnerSourceRuntimeTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        $this->migrate();
        $this->connection->exec('TRUNCATE reservation_lifecycle.availability_intent_revisions');
    }

    public function test_runtime_reports_availability_unavailability_recovery_and_corruption(): void
    {
        $runtime = $this->runtime();
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec('DROP TABLE reservation_lifecycle.availability_intent_revisions');
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeAvailability::DependencyUnavailable, $runtime->availability());

        $this->migrate();
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeAvailability::Available, $runtime->availability());

        $this->connection->exec("INSERT INTO reservation_lifecycle.availability_intent_revisions (availability_intent_id, revision, availability_subject_id, window_start, window_end, decision, effective_at, recorded_at, revision_checksum) VALUES ('00000000-0000-4000-8000-000000005409', 1, '00000000-0000-4000-8000-000000005410', '9999-12-30 00:00:00+00', '9999-12-30 01:00:00+00', 'proposed', '9999-12-29 00:00:00+00', '9999-12-29 00:00:00+00', repeat('0', 64))");
        self::assertSame(ReservationAvailabilityOwnerSourceRuntimeAvailability::Corrupted, $runtime->availability());
    }

    private function runtime(): DeterministicReservationAvailabilityOwnerSourceRuntimeV1
    {
        return new DeterministicReservationAvailabilityOwnerSourceRuntimeV1(
            new DeterministicReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy(
                new PostgreSqlReservationAvailabilityOwnerSource(
                    $this->connection,
                    new ReservationAvailabilityOwnerSourceMapper,
                ),
            ),
        );
    }

    private function migrate(): void
    {
        $root = dirname(__DIR__, 3).'/src/Modules/ReservationLifecycle/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'074_reservation_availability_owner_local_source.sql'));
    }
}
