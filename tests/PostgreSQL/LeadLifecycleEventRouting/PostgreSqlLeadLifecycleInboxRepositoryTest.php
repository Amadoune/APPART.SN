<?php

namespace Tests\PostgreSQL\LeadLifecycleEventRouting;

use App\Application\LeadLifecycleEventRouting\DurableLeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStoreStatus;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingStatus;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use App\Infrastructure\LeadLifecycleEventRouting\PostgreSql\PostgreSqlLeadLifecycleInboxRepository;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlLeadLifecycleInboxRepositoryTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_valid_envelope_is_durably_stored_byte_for_byte(): void
    {
        $envelope = $this->envelope();
        $result = $this->router()->route($envelope);
        $row = $this->connection->query('SELECT * FROM contacts_leads.lead_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);

        self::assertSame(LeadLifecycleEventRoutingStatus::Routed, $result->status);
        self::assertTrue($result->acknowledgesDelivery());
        self::assertIsArray($row);
        self::assertSame($envelope->messageId, $row['message_id']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new LeadLifecycleTransportSerializer)->serialize($envelope), $row['transport_envelope']);
        self::assertSame('pending', $row['status']);
        self::assertSame(0, (int) $row['delivery_attempts']);
    }

    public function test_identical_replay_is_idempotent_and_divergence_is_rejected(): void
    {
        $envelope = $this->envelope();
        self::assertSame(LeadLifecycleInboxStoreStatus::Stored, $this->repository()->store($envelope)->status);
        self::assertSame(LeadLifecycleInboxStoreStatus::AlreadyStored, $this->repository()->store($envelope)->status);

        $this->connection->exec("UPDATE contacts_leads.lead_lifecycle_event_inbox SET transport_envelope='divergent'");
        $result = $this->router()->route($envelope);

        self::assertSame(LeadLifecycleEventRoutingStatus::Rejected, $result->status);
        self::assertSame(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent, $result->diagnostic);
        self::assertFalse($result->acknowledgesDelivery());
        self::assertSame(1, $this->countRows());
    }

    public function test_external_transaction_rollback_removes_the_complete_message(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(LeadLifecycleInboxStoreStatus::Stored, $this->repository()->store($this->envelope())->status);
        self::assertSame(1, $this->countRows());
        $this->connection->rollBack();

        self::assertSame(0, $this->countRows());
    }

    public function test_migration_and_rollback_are_isolated_and_reversible(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'025_lead_lifecycle_event_inbox.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_event_inbox')")->fetchColumn());
        self::assertSame('contacts_leads.lead_lifecycle_transitions', $this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_transitions')")->fetchColumn());
        $this->connection->exec((string) file_get_contents($root.'025_lead_lifecycle_event_inbox.sql'));
        self::assertSame('contacts_leads.lead_lifecycle_event_inbox', $this->connection->query("SELECT to_regclass('contacts_leads.lead_lifecycle_event_inbox')")->fetchColumn());
    }

    public function test_concurrent_identical_routes_converge_to_one_durable_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-lead-routing-'.bin2hex(random_bytes(8));
        $processes = [];
        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Lead routing worker.');
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
                throw new RuntimeException('Lead routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countRows());
        $row = $this->connection->query('SELECT canonical_event,transport_envelope FROM contacts_leads.lead_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($this->envelope()->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new LeadLifecycleTransportSerializer)->serialize($this->envelope()), $row['transport_envelope']);
    }

    private function router(): DurableLeadLifecycleEventRouter
    {
        return new DurableLeadLifecycleEventRouter($this->repository());
    }

    private function repository(): PostgreSqlLeadLifecycleInboxRepository
    {
        return new PostgreSqlLeadLifecycleInboxRepository($this->connection, new LeadLifecycleTransportSerializer);
    }

    private function countRows(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM contacts_leads.lead_lifecycle_event_inbox')->fetchColumn();
    }

    private function envelope(): LeadLifecycleTransportEnvelope
    {
        $transition = new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver);
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $version = LeadLifecycleEventPayloadVersion::V1;
        $eventId = LeadLifecycleEventId::derive(LeadLifecycleEventType::Delivered, $version, $leadId, $transition, 2);
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));
        $event = new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata(LeadLifecycleEventType::Delivered, $version, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new LeadLifecycleEventPayload($eventId, $leadId, 'created>deliver>delivered', $transition->from, $transition->to, $transition->action, 1, 2),
        );

        return LeadLifecycleTransportEnvelope::wrap(new LeadLifecycleDeliveryPayload($event));
    }
}
