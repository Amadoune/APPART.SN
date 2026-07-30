<?php

namespace Tests\Unit\PropertyLifecycleEventIntegration;

use App\Application\PropertyLifecycleEventIntegration\AtomicPropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleAtomicTransaction;
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
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationDiagnosticCode;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationStatus;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AtomicPropertyLifecycleEventOrchestratorTest extends TestCase
{
    public function test_already_applied_produces_the_same_deterministic_outbox_message_once(): void
    {
        $transition = new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate);
        $writer = new CapturingPropertyLifecycleOutboxWriter(PublicProjectionOutboxWriteResult::AlreadyApplied);
        $result = $this->integrator(PropertyLifecycleOrchestrationResult::alreadyApplied($transition), $writer)->transition($this->request());

        self::assertSame(PropertyLifecycleOrchestrationStatus::AlreadyApplied, $result->status);
        self::assertCount(1, $writer->messages);
        self::assertSame('property.lifecycle.activated', $writer->messages[0]->eventType->value);
        self::assertSame(2, $writer->messages[0]->order->aggregateVersion);
        self::assertSame(1, $writer->messages[0]->order->eventIndex->value);
    }

    public function test_persistence_failure_is_propagated_without_event_or_outbox_write(): void
    {
        $writer = new CapturingPropertyLifecycleOutboxWriter(PublicProjectionOutboxWriteResult::Applied);
        $expected = PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure);
        $result = $this->integrator($expected, $writer)->transition($this->request());

        self::assertSame($expected, $result);
        self::assertSame([], $writer->messages);
    }

    private function integrator(PropertyLifecycleOrchestrationResult $result, PublicProjectionOutboxWriter $writer): AtomicPropertyLifecycleEventOrchestrator
    {
        return new AtomicPropertyLifecycleEventOrchestrator(
            new StubPropertyLifecycleOrchestrator($result),
            new ImmediatePropertyLifecycleAtomicTransaction,
            new PropertyLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('public-projection-updater'),
        );
    }

    private function request(): PropertyLifecycleEventOrchestrationRequest
    {
        return new PropertyLifecycleEventOrchestrationRequest(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            PropertyLifecycleAction::Activate,
            1,
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
            PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
        );
    }
}

final readonly class StubPropertyLifecycleOrchestrator implements PropertyLifecycleOrchestrator
{
    public function __construct(private PropertyLifecycleOrchestrationResult $result) {}

    public function transition(PropertyLifecycleOrchestrationRequest $request): PropertyLifecycleOrchestrationResult
    {
        return $this->result;
    }
}

final readonly class ImmediatePropertyLifecycleAtomicTransaction implements PropertyLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}

final class CapturingPropertyLifecycleOutboxWriter implements PublicProjectionOutboxWriter
{
    /** @var list<PublicProjectionDeliveryMessage> */
    public array $messages = [];

    public function __construct(private readonly PublicProjectionOutboxWriteResult $result) {}

    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        $this->messages[] = $message;

        return $this->result;
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
