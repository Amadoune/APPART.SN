<?php

namespace Tests\PostgreSQL\ListingPublicationEventIntegration;

use App\Application\ListingPublicationEventIntegration\AtomicListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
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
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlListingPublicationEventIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_transition_and_outbox_message_commit_atomically_with_exact_metadata(): void
    {
        $id = $this->listingId();
        $store = $this->store();
        $store->initialize($id, ListingPublicationState::Draft);
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($this->request(ListingPublicationAction::Submit));

        self::assertSame(ListingPublicationOrchestrationStatus::Applied, $result->status);
        self::assertSame(ListingPublicationState::Submitted, $store->read($id)->snapshot?->state);
        self::assertSame(2, $store->read($id)->snapshot?->version);
        $row = $this->connection->query("SELECT event_type,payload FROM listing_lifecycle.public_projection_outbox_messages WHERE aggregate_id='{$id->value}'")->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);
        self::assertSame('listing.publication.submitted', $row['event_type']);
        $payload = json_decode($row['payload'], true, flags: JSON_THROW_ON_ERROR);
        $canonical = json_decode($payload['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('2026-07-20T10:00:00.000000Z', $canonical['metadata']['occurredAt']);
        self::assertSame('2026-07-20T10:00:01.000000Z', $canonical['metadata']['recordedAt']);
    }

    public function test_denied_transition_writes_neither_transition_nor_outbox(): void
    {
        $id = $this->listingId();
        $store = $this->store();
        $store->initialize($id, ListingPublicationState::Draft);
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($this->request(ListingPublicationAction::Expire));

        self::assertSame(ListingPublicationOrchestrationStatus::Denied, $result->status);
        self::assertSame(1, $store->read($id)->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_outbox_rejection_rolls_back_the_persisted_transition(): void
    {
        $id = $this->listingId();
        $store = $this->store();
        $store->initialize($id, ListingPublicationState::Draft);
        $result = $this->orchestrator($store, new RejectingIntegrationOutboxWriter)->transition($this->request(ListingPublicationAction::Submit));

        self::assertSame(ListingPublicationOrchestrationStatus::PersistenceFailure, $result->status);
        self::assertSame(ListingPublicationState::Draft, $store->read($id)->snapshot?->state);
        self::assertSame(1, $store->read($id)->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_stale_version_emits_nothing(): void
    {
        $id = $this->listingId();
        $store = $this->store();
        $store->initialize($id, ListingPublicationState::Draft);
        $request = new ListingPublicationEventOrchestrationRequest(new ListingPublicationOrchestrationRequest($id, ListingPublicationAction::Submit, 2), $this->metadata());
        $result = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper))->transition($request);

        self::assertSame(ListingPublicationOrchestrationStatus::ConcurrencyConflict, $result->status);
        self::assertSame(1, $store->read($id)->snapshot?->version);
        self::assertSame(0, $this->outboxCount());
    }

    public function test_retry_cannot_duplicate_transition_or_outbox_message(): void
    {
        $id = $this->listingId();
        $store = $this->store();
        $store->initialize($id, ListingPublicationState::Draft);
        $orchestrator = $this->orchestrator($store, new PostgreSqlPublicProjectionOutboxWriter($this->connection, new PostgreSqlPublicProjectionOutboxMapper));
        self::assertSame(ListingPublicationOrchestrationStatus::Applied, $orchestrator->transition($this->request(ListingPublicationAction::Submit))->status);
        self::assertSame(ListingPublicationOrchestrationStatus::ConcurrencyConflict, $orchestrator->transition($this->request(ListingPublicationAction::Submit))->status);

        self::assertSame(2, $store->read($id)->snapshot?->version);
        self::assertSame(1, $this->outboxCount());
    }

    private function orchestrator(PostgreSqlListingPublicationWorkflowRepository $store, PublicProjectionOutboxWriter $writer): AtomicListingPublicationEventOrchestrator
    {
        return new AtomicListingPublicationEventOrchestrator(
            new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $store),
            new PostgreSqlAggregateOutboxTransaction($this->connection),
            new ListingPublicationEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
        );
    }

    private function store(): PostgreSqlListingPublicationWorkflowRepository
    {
        return new PostgreSqlListingPublicationWorkflowRepository($this->connection, new ListingPublicationWorkflowMapper);
    }

    private function request(ListingPublicationAction $action): ListingPublicationEventOrchestrationRequest
    {
        return new ListingPublicationEventOrchestrationRequest(new ListingPublicationOrchestrationRequest($this->listingId(), $action, 1), $this->metadata());
    }

    private function metadata(): ListingPublicationEventMetadata
    {
        return new ListingPublicationEventMetadata(ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'), ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'));
    }

    private function listingId(): ListingId
    {
        return ListingId::fromString('11111111-1111-4111-8111-111111111111');
    }

    private function outboxCount(): int
    {
        return (int) $this->connection->query('SELECT count(*) FROM listing_lifecycle.public_projection_outbox_messages')->fetchColumn();
    }
}

final readonly class RejectingIntegrationOutboxWriter implements PublicProjectionOutboxWriter
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
