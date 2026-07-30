<?php

namespace Tests\PostgreSQL\ReservationLifecycleEventRouting;

use App\Application\ReservationLifecycleEventRouting\DeterministicReservationLifecycleEventRouter;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use App\Infrastructure\ReservationLifecycleEventRouting\PostgreSql\PostgreSqlReservationLifecycleInboxRepository;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlReservationLifecycleInboxRepositoryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_valid_envelope_is_stored_once_without_any_payload_alteration(): void
    {
        $envelope = $this->envelope();
        $result = $this->router()->route($envelope);
        $row = $this->connection->query('SELECT * FROM reservation_lifecycle.reservation_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);

        self::assertSame(ReservationLifecycleRoutingStatus::Stored, $result->status);
        self::assertIsArray($row);
        self::assertStringStartsWith('rlei:', $row['inbox_id']);
        self::assertSame($envelope->messageId, $row['message_id']);
        self::assertSame($envelope->messageType, $row['message_type']);
        self::assertSame($envelope->transportVersion, (int) $row['transport_version']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame($envelope->metadata->source, $row['source']);
        self::assertSame($envelope->metadata->businessEventId, $row['business_event_id']);
        self::assertSame($envelope->metadata->payloadChecksum, $row['payload_checksum']);
        self::assertSame((new ReservationLifecycleTransportSerializer)->serialize($envelope), $row['transport_envelope']);
    }

    public function test_identical_message_is_transactionally_idempotent(): void
    {
        $envelope = $this->envelope();

        self::assertSame(ReservationLifecycleRoutingStatus::Stored, $this->router()->route($envelope)->status);
        self::assertSame(ReservationLifecycleRoutingStatus::AlreadyStored, $this->router()->route($envelope)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_event_inbox')->fetchColumn());
    }

    public function test_existing_message_id_with_divergent_content_is_corrupted(): void
    {
        $envelope = $this->envelope();
        self::assertSame(ReservationLifecycleRoutingStatus::Stored, $this->router()->route($envelope)->status);
        $statement = $this->connection->prepare("UPDATE reservation_lifecycle.reservation_lifecycle_event_inbox SET transport_envelope='divergent' WHERE message_id=:message_id");
        $statement->execute(['message_id' => $envelope->messageId]);

        self::assertSame(ReservationLifecycleRoutingStatus::CorruptedEnvelope, $this->router()->route($envelope)->status);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_event_inbox')->fetchColumn());
    }

    public function test_stored_canonical_event_restores_the_exact_transport_envelope(): void
    {
        $envelope = $this->envelope();
        $this->router()->route($envelope);
        $row = $this->connection->query('SELECT * FROM reservation_lifecycle.reservation_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        $payload = ReservationLifecycleDeliveryPayload::restore(['canonicalEvent' => $row['canonical_event']]);
        $restored = ReservationLifecycleTransportEnvelope::wrap($payload);

        self::assertEquals($envelope, $restored);
        self::assertSame($row['transport_envelope'], (new ReservationLifecycleTransportSerializer)->serialize($restored));
        self::assertSame($row['canonical_event'], $restored->payload->fields()['canonicalEvent']);
    }

    public function test_message_id_unique_constraint_rejects_a_duplicate_direct_write(): void
    {
        $envelope = $this->envelope();
        $this->router()->route($envelope);

        $this->expectException(PDOException::class);
        $this->connection->exec("INSERT INTO reservation_lifecycle.reservation_lifecycle_event_inbox (inbox_id,message_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope) SELECT 'rlei:' || repeat('0',64),message_id,message_type,transport_version,canonical_event,source,business_event_id,payload_checksum,transport_envelope FROM reservation_lifecycle.reservation_lifecycle_event_inbox");
    }

    public function test_restore_lookup_uses_the_dedicated_index(): void
    {
        $this->connection->exec('SET enable_seqscan = off');
        $plan = implode("\n", $this->connection->query("EXPLAIN (FORMAT TEXT) SELECT message_id FROM reservation_lifecycle.reservation_lifecycle_event_inbox WHERE message_type='reservation.lifecycle.submitted' AND transport_version=1 ORDER BY message_id LIMIT 1")->fetchAll(PDO::FETCH_COLUMN));

        self::assertStringContainsString('reservation_lifecycle_event_inbox_restore_lookup', $plan);
    }

    public function test_migration_and_rollback_are_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/ReservationLifecycleEventRouting/PostgreSql/Migrations/';
        $down = (string) file_get_contents($root.'020_reservation_lifecycle_event_inbox.down.sql');
        $up = (string) file_get_contents($root.'020_reservation_lifecycle_event_inbox.sql');

        $this->connection->exec($down);
        self::assertNull($this->connection->query("SELECT to_regclass('reservation_lifecycle.reservation_lifecycle_event_inbox')")->fetchColumn());
        $this->connection->exec($up);
        self::assertSame('reservation_lifecycle.reservation_lifecycle_event_inbox', $this->connection->query("SELECT to_regclass('reservation_lifecycle.reservation_lifecycle_event_inbox')")->fetchColumn());
    }

    public function test_concurrent_identical_routes_converge_to_one_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-reservation-routing-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start reservation routing worker.');
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
                throw new RuntimeException('Reservation routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, (int) $this->connection->query('SELECT count(*) FROM reservation_lifecycle.reservation_lifecycle_event_inbox')->fetchColumn());
    }

    private function router(): DeterministicReservationLifecycleEventRouter
    {
        return new DeterministicReservationLifecycleEventRouter(
            new PostgreSqlReservationLifecycleInboxRepository($this->connection, new ReservationLifecycleTransportSerializer),
        );
    }

    private function envelope(): ReservationLifecycleTransportEnvelope
    {
        $event = (new ReservationLifecycleEventCatalog)->eventFor(
            ReservationId::fromString('22222222-2222-4222-8222-222222222222'),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            2,
        );

        return ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($event));
    }
}
