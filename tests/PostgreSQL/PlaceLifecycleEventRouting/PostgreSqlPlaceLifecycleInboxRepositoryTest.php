<?php

namespace Tests\PostgreSQL\PlaceLifecycleEventRouting;

use App\Application\PlaceLifecycleEventRouting\DurablePlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStoreStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingDiagnostic;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use App\Infrastructure\PlaceLifecycleEventRouting\PostgreSql\PostgreSqlPlaceLifecycleInboxRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\Geography\DurablePlaceLifecycleEventRouterTest;

final class PostgreSqlPlaceLifecycleInboxRepositoryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_valid_envelope_is_stored_exactly(): void
    {
        $envelope = DurablePlaceLifecycleEventRouterTest::envelope();
        $result = (new DurablePlaceLifecycleEventRouter($this->repository()))->route($envelope);
        $row = $this->connection->query(
            'SELECT * FROM geography.place_lifecycle_event_inbox',
        )->fetch(PDO::FETCH_ASSOC);

        self::assertSame(PlaceLifecycleEventRoutingStatus::Routed, $result->status);
        self::assertIsArray($row);
        self::assertSame($envelope->messageId->value, $row['message_id']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new PlaceLifecycleTransportSerializer)->serialize($envelope), $row['transport_envelope']);
        self::assertSame('pending', $row['status']);
    }

    public function test_identical_replay_is_idempotent_and_divergence_is_rejected(): void
    {
        $envelope = DurablePlaceLifecycleEventRouterTest::envelope();

        self::assertSame(PlaceLifecycleInboxStoreStatus::Stored, $this->repository()->store($envelope)->status);
        self::assertSame(PlaceLifecycleInboxStoreStatus::AlreadyStored, $this->repository()->store($envelope)->status);
        $this->connection->exec(
            "UPDATE geography.place_lifecycle_event_inbox SET transport_envelope='divergent'",
        );
        $result = (new DurablePlaceLifecycleEventRouter($this->repository()))->route($envelope);
        self::assertSame(PlaceLifecycleEventRoutingStatus::Rejected, $result->status);
        self::assertSame(PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent, $result->diagnostic);
        self::assertSame(1, $this->countRows());
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(
            PlaceLifecycleInboxStoreStatus::Stored,
            $this->repository()->store(DurablePlaceLifecycleEventRouterTest::envelope())->status,
        );
        self::assertSame(1, $this->countRows());
        $this->connection->rollBack();
        self::assertSame(0, $this->countRows());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'039_place_lifecycle_event_inbox.down.sql'));
        self::assertNull(
            $this->connection->query(
                "SELECT to_regclass('geography.place_lifecycle_event_inbox')",
            )->fetchColumn(),
        );
        self::assertSame(
            'geography.place_lifecycle_transitions',
            $this->connection->query(
                "SELECT to_regclass('geography.place_lifecycle_transitions')",
            )->fetchColumn(),
        );
        $this->connection->exec((string) file_get_contents($root.'039_place_lifecycle_event_inbox.sql'));
        self::assertSame(
            'geography.place_lifecycle_event_inbox',
            $this->connection->query(
                "SELECT to_regclass('geography.place_lifecycle_event_inbox')",
            )->fetchColumn(),
        );
    }

    public function test_concurrent_identical_routes_converge_to_one_row(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-place-routing-'.bin2hex(random_bytes(8));
        $processes = [];

        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Place routing worker.');
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
            $error = trim(stream_get_contents($pipes[2]));
            if (proc_close($process) !== 0 || $error !== '') {
                throw new RuntimeException('Place routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countRows());
    }

    private function repository(): PostgreSqlPlaceLifecycleInboxRepository
    {
        return new PostgreSqlPlaceLifecycleInboxRepository(
            $this->connection,
            new PlaceLifecycleTransportSerializer,
        );
    }

    private function countRows(): int
    {
        return (int) $this->connection->query(
            'SELECT count(*) FROM geography.place_lifecycle_event_inbox',
        )->fetchColumn();
    }
}
