<?php

namespace Tests\PostgreSQL\ProfessionalStatusEventRouting;

use App\Application\ProfessionalStatusEventRouting\DurableProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStoreStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use App\Infrastructure\ProfessionalStatusEventRouting\PostgreSql\PostgreSqlProfessionalStatusInboxRepository;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlProfessionalStatusInboxRepositoryTest extends TestCase
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
        $row = $this->connection->query('SELECT * FROM professionals.professional_status_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertSame(ProfessionalStatusEventRoutingStatus::Routed, $result->status);
        self::assertTrue($result->acknowledgesDelivery());
        self::assertIsArray($row);
        self::assertSame($envelope->messageId, $row['message_id']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new ProfessionalStatusTransportSerializer)->serialize($envelope), $row['transport_envelope']);
        self::assertSame('pending', $row['status']);
    }

    public function test_identical_replay_is_idempotent_and_divergence_rejected(): void
    {
        $envelope = $this->envelope();
        self::assertSame(ProfessionalStatusInboxStoreStatus::Stored, $this->repository()->store($envelope)->status);
        self::assertSame(ProfessionalStatusInboxStoreStatus::AlreadyStored, $this->repository()->store($envelope)->status);
        $this->connection->exec("UPDATE professionals.professional_status_event_inbox SET transport_envelope='divergent'");
        $result = $this->router()->route($envelope);
        self::assertSame(ProfessionalStatusEventRoutingStatus::Rejected, $result->status);
        self::assertSame(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent, $result->diagnostic);
        self::assertSame(1, $this->countRows());
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(ProfessionalStatusInboxStoreStatus::Stored, $this->repository()->store($this->envelope())->status);
        self::assertSame(1, $this->countRows());
        $this->connection->rollBack();
        self::assertSame(0, $this->countRows());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/ProfessionalStatusEventRouting/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'029_professional_status_event_inbox.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('professionals.professional_status_event_inbox')")->fetchColumn());
        self::assertSame('professionals.professional_status_transitions', $this->connection->query("SELECT to_regclass('professionals.professional_status_transitions')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'029_professional_status_event_inbox.sql'));
        self::assertSame('professionals.professional_status_event_inbox', $this->connection->query("SELECT to_regclass('professionals.professional_status_event_inbox')")->fetchColumn());
    }

    public function test_concurrent_identical_routes_converge_to_one_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-professional-routing-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start professional routing worker.');
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
                throw new RuntimeException('Professional routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }
        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countRows());
        $row = $this->connection->query('SELECT canonical_event,transport_envelope FROM professionals.professional_status_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($this->envelope()->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new ProfessionalStatusTransportSerializer)->serialize($this->envelope()), $row['transport_envelope']);
    }

    private function router(): DurableProfessionalStatusEventRouter
    {
        return new DurableProfessionalStatusEventRouter($this->repository());
    }

    private function repository(): PostgreSqlProfessionalStatusInboxRepository
    {
        return new PostgreSqlProfessionalStatusInboxRepository($this->connection, new ProfessionalStatusTransportSerializer);
    }

    private function countRows(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM professionals.professional_status_event_inbox')->fetchColumn();
    }

    private function envelope(): ProfessionalStatusTransportEnvelope
    {
        $transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
        $id = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $eventId = ProfessionalStatusEventId::derive(ProfessionalStatusEventType::Suspended, $version, $id, $transition, 2);
        $at = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new ProfessionalStatusEvent(new ProfessionalStatusEventMetadata(ProfessionalStatusEventType::Suspended, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new ProfessionalStatusEventPayload($eventId, $id, 'active>suspend>suspended', $transition->from, $transition->to, $transition->action, 1, 2));

        return ProfessionalStatusTransportEnvelope::wrap(new ProfessionalStatusDeliveryPayload($event));
    }
}
