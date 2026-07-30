<?php

namespace Tests\PostgreSQL\PlaceLifecycleEventIntegration;

use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventIntegrationFailure;
use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventOrchestrator;
use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxMapper;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleCurrentState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationRequest;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationStatus;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrator;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Application\PlaceMergeContext\DeterministicPlaceMergeReplayClassifier;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceMergeContextInspector;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlPlaceLifecycleAtomicEventIntegrationTest extends TestCase
{
    private PDO $connection;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
    }

    public function test_transition_context_event_delivery_and_outbox_commit_atomically_and_idempotently(): void
    {
        $store = $this->store();
        $request = $this->request();
        $this->initialize($store, $request);
        $orchestrator = $this->atomic($store);

        self::assertSame(PlaceLifecycleOrchestrationStatus::Applied, $orchestrator->transition($request)->status);
        self::assertSame(PlaceLifecycleOrchestrationStatus::AlreadyApplied, $orchestrator->transition($request)->status);
        self::assertSame(1, $this->countRows('geography.place_lifecycle_transitions', 'entry_kind = \'transition\''));
        self::assertSame(1, $this->countRows('geography.public_projection_outbox_messages'));
        self::assertSame(1, $this->countRows('geography.public_projection_outbox_deliveries'));
        self::assertSame('place.lifecycle.merged', $this->scalar('SELECT event_type FROM geography.public_projection_outbox_messages'));
    }

    public function test_workflow_refusal_writes_neither_transition_nor_outbox(): void
    {
        $store = $this->store();
        $request = $this->request(PlaceLifecycleAction::Enable);
        $this->initialize($store, $request);

        self::assertSame(
            PlaceLifecycleOrchestrationStatus::WorkflowRefused,
            $this->atomic($store)->transition($request)->status,
        );
        self::assertSame(0, $this->countRows('geography.place_lifecycle_transitions', 'entry_kind = \'transition\''));
        self::assertSame(0, $this->countRows('geography.public_projection_outbox_messages'));
    }

    public function test_rejected_outbox_write_rolls_back_transition_and_context(): void
    {
        $store = $this->store();
        $request = $this->request();
        $this->initialize($store, $request);
        $orchestrator = $this->atomic($store, new RejectingPlaceLifecycleOutboxWriter);

        try {
            $orchestrator->transition($request);
            self::fail('A rejected Outbox write must fail the atomic integration.');
        } catch (PlaceLifecycleAtomicEventIntegrationFailure) {
            self::assertSame(0, $this->countRows('geography.place_lifecycle_transitions', 'entry_kind = \'transition\''));
            self::assertSame(0, $this->countRows('geography.public_projection_outbox_messages'));
        }
    }

    private function request(
        PlaceLifecycleAction $action = PlaceLifecycleAction::Merge,
    ): PlaceLifecycleAtomicEventRequest {
        $source = PlaceId::fromString('48000000-0000-4000-8000-000000000001');
        $target = PlaceId::fromString('48000000-0000-4000-8000-000000000002');
        $at = new DateTimeImmutable('2026-07-25T14:00:00.123456+00:00');
        $context = new PlaceMergeContextV1(
            $source,
            $target,
            new PlaceMergeExpectedSourceVersion(1),
            new PlaceMergeObservedTargetVersion(5),
            PlaceMergeObservedState::Enabled,
            PlaceType::City,
            PlaceType::City,
            CountryCode::fromString('SN'),
            CountryCode::fromString('SN'),
            PlaceMergeActorId::fromString('48000000-0000-4000-8000-000000000003'),
            PlaceMergeOccurredAt::fromExplicitUtc($at),
            PlaceMergeIntentId::fromString('48000000-0000-4000-8000-000000000004'),
        );

        return new PlaceLifecycleAtomicEventRequest(
            new PlaceLifecycleOrchestrationRequest(
                new PlaceLifecycleCurrentState($source, PlaceLifecycleState::Enabled),
                $action,
                $context,
            ),
            $at->modify('+1 second'),
        );
    }

    private function initialize(
        PlaceLifecycleWorkflowStore $store,
        PlaceLifecycleAtomicEventRequest $request,
    ): void {
        $store->initialize($request->transition->context->sourceId, PlaceLifecycleState::Enabled, 1);
        $store->initialize($request->transition->context->targetId, PlaceLifecycleState::Enabled, 5);
    }

    private function store(): PostgreSqlPlaceLifecycleWorkflowStore
    {
        return new PostgreSqlPlaceLifecycleWorkflowStore(
            $this->connection,
            new PlaceLifecycleWorkflowMapper,
        );
    }

    private function atomic(
        PostgreSqlPlaceLifecycleWorkflowStore $store,
        ?PublicProjectionOutboxWriter $writer = null,
    ): PlaceLifecycleAtomicEventOrchestrator {
        $mapper = new PlaceLifecycleWorkflowMapper;

        return new PlaceLifecycleAtomicEventOrchestrator(
            new PlaceLifecycleOrchestrator(
                new PostgreSqlPlaceMergeContextInspector($this->connection, $mapper),
                new DeterministicPlaceMergeReplayClassifier,
                new PlaceLifecycleWorkflow,
                $store,
            ),
            new PostgreSqlAggregateOutboxTransaction($this->connection),
            new PlaceLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $writer ?? new PostgreSqlPublicProjectionOutboxWriter(
                $this->connection,
                new PostgreSqlPublicProjectionOutboxMapper,
            ),
            PublicProjectionOutboxConsumerId::fromString('place-lifecycle-atomic-test'),
        );
    }

    private function countRows(string $table, ?string $where = null): int
    {
        return (int) $this->scalar('SELECT count(*) FROM '.$table.($where === null ? '' : ' WHERE '.$where));
    }

    private function scalar(string $sql): mixed
    {
        return $this->connection->query($sql)->fetchColumn();
    }
}

final class RejectingPlaceLifecycleOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(
        PublicProjectionDeliveryMessage $message,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function markDelivered($message, $consumerId, $ownerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function scheduleRetry($message, $consumerId, $ownerId, $decision): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function quarantine($message, $consumerId, $ownerId, $decision): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }

    public function releaseClaim($message, $consumerId, $ownerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::DivergentMessage;
    }
}
