<?php

namespace Tests\PostgreSQL\AdministrativeActionLifecycleEventRouting;

use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStoreStatus;
use App\Application\AdministrativeActionLifecycleEventRouting\DurableAdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingStatus;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use App\Infrastructure\AdministrativeActionLifecycleEventRouting\PostgreSql\PostgreSqlAdministrativeActionLifecycleInboxRepository;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlAdministrativeActionLifecycleInboxRepositoryTest extends TestCase
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
        $row = $this->connection->query('SELECT * FROM administration_audit.administrative_action_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);

        self::assertSame(AdministrativeActionLifecycleEventRoutingStatus::Routed, $result->status);
        self::assertTrue($result->acknowledgesDelivery());
        self::assertIsArray($row);
        self::assertSame($envelope->messageId, $row['message_id']);
        self::assertSame($envelope->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new AdministrativeActionLifecycleTransportSerializer)->serialize($envelope), $row['transport_envelope']);
        self::assertSame('pending', $row['status']);
    }

    public function test_identical_replay_is_idempotent_and_divergence_rejected(): void
    {
        $envelope = $this->envelope();

        self::assertSame(AdministrativeActionLifecycleInboxStoreStatus::Stored, $this->repository()->store($envelope)->status);
        self::assertSame(AdministrativeActionLifecycleInboxStoreStatus::AlreadyStored, $this->repository()->store($envelope)->status);
        $this->connection->exec("UPDATE administration_audit.administrative_action_lifecycle_event_inbox SET transport_envelope='divergent'");
        $result = $this->router()->route($envelope);
        self::assertSame(AdministrativeActionLifecycleEventRoutingStatus::Rejected, $result->status);
        self::assertSame(AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent, $result->diagnostic);
        self::assertSame(1, $this->countRows());
    }

    public function test_external_transaction_rollback_is_complete(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(AdministrativeActionLifecycleInboxStoreStatus::Stored, $this->repository()->store($this->envelope())->status);
        self::assertSame(1, $this->countRows());
        $this->connection->rollBack();
        self::assertSame(0, $this->countRows());
    }

    public function test_migration_and_rollback_are_isolated(): void
    {
        $root = dirname(__DIR__, 3).'/app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($root.'036_administrative_action_lifecycle_event_inbox.down.sql'));
        self::assertNull($this->connection->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_event_inbox')")->fetchColumn());
        self::assertSame(
            'administration_audit.administrative_action_lifecycle_transitions',
            $this->connection->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_transitions')")->fetchColumn(),
        );
        $this->connection->exec((string) file_get_contents($root.'036_administrative_action_lifecycle_event_inbox.sql'));
        self::assertSame(
            'administration_audit.administrative_action_lifecycle_event_inbox',
            $this->connection->query("SELECT to_regclass('administration_audit.administrative_action_lifecycle_event_inbox')")->fetchColumn(),
        );
    }

    public function test_concurrent_identical_routes_converge_to_one_message(): void
    {
        $barrier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'appart-administrative-action-routing-'.bin2hex(random_bytes(8));
        $processes = [];

        foreach ([1, 2] as $number) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, __DIR__.DIRECTORY_SEPARATOR.'concurrency-worker.php', $barrier, (string) $number],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
            );
            if (! is_resource($process)) {
                throw new RuntimeException('Unable to start Administrative Action routing worker.');
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
                throw new RuntimeException('Administrative Action routing worker failed: '.$error);
            }
        }
        sort($results);
        foreach (['.ready.1', '.ready.2', '.start'] as $suffix) {
            @unlink($barrier.$suffix);
        }

        self::assertSame(['already_stored', 'stored'], $results);
        self::assertSame(1, $this->countRows());
        $row = $this->connection->query('SELECT canonical_event,transport_envelope FROM administration_audit.administrative_action_lifecycle_event_inbox')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame($this->envelope()->payload->fields()['canonicalEvent'], $row['canonical_event']);
        self::assertSame((new AdministrativeActionLifecycleTransportSerializer)->serialize($this->envelope()), $row['transport_envelope']);
    }

    private function router(): DurableAdministrativeActionLifecycleEventRouter
    {
        return new DurableAdministrativeActionLifecycleEventRouter($this->repository());
    }

    private function repository(): PostgreSqlAdministrativeActionLifecycleInboxRepository
    {
        return new PostgreSqlAdministrativeActionLifecycleInboxRepository(
            $this->connection,
            new AdministrativeActionLifecycleTransportSerializer,
        );
    }

    private function countRows(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM administration_audit.administrative_action_lifecycle_event_inbox')->fetchColumn();
    }

    private function envelope(): AdministrativeActionLifecycleTransportEnvelope
    {
        $transition = new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::Recorded, AdministrativeActionLifecycleAction::Record);
        $actionId = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $eventId = AdministrativeActionLifecycleEventId::derive(AdministrativeActionLifecycleEventType::Recorded, $version, $actionId, $transition, 2);
        $at = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00+00:00'));
        $event = new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata(AdministrativeActionLifecycleEventType::Recorded, $version, ActorId::fromString('decision-actor-001'), $at, $at),
            new AdministrativeActionLifecycleEventPayload($eventId, $actionId, 'draft>record>recorded', $transition->from, $transition->to, $transition->action, 1, 2),
        );

        return AdministrativeActionLifecycleTransportEnvelope::wrap(new AdministrativeActionLifecycleDeliveryPayload($event));
    }
}
