<?php

namespace Tests\PostgreSQL\PropertyLifecycleEventIntegration;

use App\Application\PropertyLifecycleEventIntegration\AtomicPropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventIntegration\PropertyLifecycleEventOrchestrationRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\DeterministicPropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyLifecycleWorkflowRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPropertyLifecycleEventIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_transition_and_exact_outbox_message_commit_atomically(): void
    {
        $store = $this->store();
        $store->initialize($this->propertyId(), PropertyLifecycleState::Draft);
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($this->request(PropertyLifecycleAction::Activate));

        self::assertSame(PropertyLifecycleOrchestrationStatus::Applied, $result->status);
        self::assertSame(PropertyLifecycleState::Active, $store->read($this->propertyId())->snapshot?->state);
        self::assertSame(2, $store->read($this->propertyId())->snapshot?->version);
        $row = $this->connection->query("SELECT message_id,event_type,payload_version,payload,payload_checksum,occurred_at,recorded_at FROM real_estate_catalog.public_projection_outbox_messages WHERE aggregate_id='{$this->propertyId()->value}'")->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame('property.lifecycle.activated', $row['event_type']);
        self::assertSame(1, $row['payload_version']);
        $payload = json_decode($row['payload'], true, flags: JSON_THROW_ON_ERROR);
        $canonical = json_decode($payload['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('2026-07-21T10:00:00.000000Z', $canonical['metadata']['occurredAt']);
        self::assertSame('2026-07-21T10:00:01.000000Z', $canonical['metadata']['recordedAt']);
        self::assertSame(hash('sha256', $payload['canonicalEvent']), $row['payload_checksum']);
        self::assertNotSame($canonical['eventId'], $row['message_id']);
    }

    public function test_denied_transition_writes_neither_transition_nor_outbox(): void
    {
        $store = $this->store();
        $store->initialize($this->propertyId(), PropertyLifecycleState::Draft);
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($this->request(PropertyLifecycleAction::BeginMaintenance));

        self::assertSame(PropertyLifecycleOrchestrationStatus::Denied, $result->status);
        self::assertSame(1, $store->read($this->propertyId())->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_outbox_rejection_rolls_back_the_transition(): void
    {
        $store = $this->store();
        $store->initialize($this->propertyId(), PropertyLifecycleState::Draft);
        $result = $this->orchestrator($store, new RejectingPropertyLifecycleOutboxWriter)->transition($this->request(PropertyLifecycleAction::Activate));

        self::assertSame(PropertyLifecycleOrchestrationStatus::PersistenceFailure, $result->status);
        self::assertSame(PropertyLifecycleState::Draft, $store->read($this->propertyId())->snapshot?->state);
        self::assertSame(1, $store->read($this->propertyId())->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_stale_version_emits_nothing(): void
    {
        $store = $this->store();
        $store->initialize($this->propertyId(), PropertyLifecycleState::Draft);
        $request = new PropertyLifecycleEventOrchestrationRequest($this->propertyId(), PropertyLifecycleAction::Activate, 2, $this->occurredAt(), $this->recordedAt());
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($request);

        self::assertSame(PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, $result->status);
        self::assertSame(1, $store->read($this->propertyId())->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_identical_replay_creates_no_duplicate_transition_or_message(): void
    {
        $store = $this->store();
        $store->initialize($this->propertyId(), PropertyLifecycleState::Draft);
        $orchestrator = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper));
        self::assertSame(PropertyLifecycleOrchestrationStatus::Applied, $orchestrator->transition($this->request(PropertyLifecycleAction::Activate))->status);
        self::assertSame(PropertyLifecycleOrchestrationStatus::ConcurrencyConflict, $orchestrator->transition($this->request(PropertyLifecycleAction::Activate))->status);

        self::assertSame(2, $store->read($this->propertyId())->snapshot?->version);
        self::assertSame(1, $this->outboxCount());
    }

    private function orchestrator(PostgreSqlPropertyLifecycleWorkflowRepository $store, PublicProjectionOutboxWriter $writer): AtomicPropertyLifecycleEventOrchestrator
    {
        return new AtomicPropertyLifecycleEventOrchestrator(
            new DeterministicPropertyLifecycleOrchestrator(new PropertyLifecycleWorkflow, $store),
            new PostgreSqlAggregateOutboxTransaction($this->connection),
            new PropertyLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
        );
    }

    private function store(): PostgreSqlPropertyLifecycleWorkflowRepository
    {
        return new PostgreSqlPropertyLifecycleWorkflowRepository($this->connection, new PropertyLifecycleWorkflowMapper);
    }

    private function request(PropertyLifecycleAction $action): PropertyLifecycleEventOrchestrationRequest
    {
        return new PropertyLifecycleEventOrchestrationRequest($this->propertyId(), $action, 1, $this->occurredAt(), $this->recordedAt());
    }

    private function occurredAt(): PropertyLifecycleEventInstant
    {
        return PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z');
    }

    private function recordedAt(): PropertyLifecycleEventInstant
    {
        return PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z');
    }

    private function propertyId(): PropertyId
    {
        return PropertyId::fromString('22222222-2222-4222-8222-222222222222');
    }

    private function outboxCount(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM real_estate_catalog.public_projection_outbox_messages')->fetchColumn();
    }
}

final readonly class RejectingPropertyLifecycleOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }
}
