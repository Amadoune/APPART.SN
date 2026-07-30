<?php

namespace Tests\PostgreSQL\MediaItemLifecycleEventRouting;

use App\Application\MediaItemLifecycleEventRouting\DurableMediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStoreStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use App\Infrastructure\MediaItemLifecycleEventRouting\PostgreSql\PostgreSqlMediaItemLifecycleInboxRepository;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlMediaItemLifecycleInboxRepositoryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_valid_envelope_is_stored_byte_for_byte(): void
    {
        $envelope = $this->envelope();
        $result = $this->router()->route($envelope);
        $row = $this->connection->query('SELECT * FROM media.media_item_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertSame(MediaItemLifecycleEventRoutingStatus::Routed, $result->status);
        self::assertTrue($result->acknowledgesDelivery());
        self::assertIsArray($row);
        self::assertSame($envelope->messageId, $row['message_id']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new MediaItemLifecycleTransportSerializer)->serialize($envelope), $row['transport_envelope']);
        self::assertSame('pending', $row['status']);
    }

    public function test_identical_replay_is_idempotent_and_divergence_rejected(): void
    {
        $envelope = $this->envelope();
        self::assertSame(MediaItemLifecycleInboxStoreStatus::Stored, $this->repository()->store($envelope)->status);
        self::assertSame(MediaItemLifecycleInboxStoreStatus::AlreadyStored, $this->repository()->store($envelope)->status);
        $this->connection->exec("UPDATE media.media_item_lifecycle_event_inbox SET transport_envelope='divergent'");
        $result = $this->router()->route($envelope);
        self::assertSame(MediaItemLifecycleEventRoutingStatus::Rejected, $result->status);
        self::assertSame(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent, $result->diagnostic);
        self::assertSame(1, $this->countRows());
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(MediaItemLifecycleInboxStoreStatus::Stored, $this->repository()->store($this->envelope())->status);
        self::assertSame(1, $this->countRows());
        $this->connection->rollBack();
        self::assertSame(0, $this->countRows());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'033_media_item_lifecycle_event_inbox.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('media.media_item_lifecycle_event_inbox')")->fetchColumn());
        self::assertSame('media.media_item_lifecycle_transitions', $this->connection->query("SELECT to_regclass('media.media_item_lifecycle_transitions')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'033_media_item_lifecycle_event_inbox.sql'));
        self::assertSame('media.media_item_lifecycle_event_inbox', $this->connection->query("SELECT to_regclass('media.media_item_lifecycle_event_inbox')")->fetchColumn());
    }

    public function test_concurrent_identical_routes_converge_to_one_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-media-item-routing-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start media item routing worker.');
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
                throw new RuntimeException('Media item routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countRows());
        $row = $this->connection->query('SELECT canonical_event,transport_envelope FROM media.media_item_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($this->envelope()->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new MediaItemLifecycleTransportSerializer)->serialize($this->envelope()), $row['transport_envelope']);
    }

    private function router(): DurableMediaItemLifecycleEventRouter
    {
        return new DurableMediaItemLifecycleEventRouter($this->repository());
    }

    private function repository(): PostgreSqlMediaItemLifecycleInboxRepository
    {
        return new PostgreSqlMediaItemLifecycleInboxRepository($this->connection, new MediaItemLifecycleTransportSerializer);
    }

    private function countRows(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM media.media_item_lifecycle_event_inbox')->fetchColumn();
    }

    private function envelope(): MediaItemLifecycleTransportEnvelope
    {
        $transition = new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove);
        $id = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $eventId = MediaItemLifecycleEventId::derive(MediaItemLifecycleEventType::Removed, $version, $id, $transition, 2);
        $at = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new MediaItemLifecycleEvent(new MediaItemLifecycleEventMetadata(MediaItemLifecycleEventType::Removed, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new MediaItemLifecycleEventPayload($eventId, $id, 'active>remove>removed', $transition->from, $transition->to, $transition->action, 1, 2));

        return MediaItemLifecycleTransportEnvelope::wrap(new MediaItemLifecycleDeliveryPayload($event));
    }
}
