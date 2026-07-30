<?php

namespace Tests\Unit\Modules\IdentityAccess;

use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventOrchestrator;
use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventRequest;
use App\Application\AccountStatusEventIntegration\Contract\AccountStatusAtomicTransaction;
use App\Application\AccountStatusEventRouting\DeterministicAccountStatusEventRouter;
use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\AccountStatusOrchestrationStatus;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AccountStatusAtomicEventIntegrationTest extends TestCase
{
    public function test_applied_transition_writes_one_routed_delivery_with_preserved_identities(): void
    {
        $writer = new CapturingAccountStatusRoutedWriter;
        $transaction = new ImmediateAccountStatusAtomicTransaction;
        $result = $this->integration(
            new FixedAccountStatusOrchestrator(AccountStatusOrchestrationStatus::Applied),
            $writer,
            $transaction,
        )->transition($this->request());

        self::assertSame(AccountStatusOrchestrationStatus::Applied, $result->status);
        self::assertSame(1, $transaction->calls);
        self::assertCount(1, $writer->deliveries);
        $delivery = $writer->deliveries[0];
        self::assertTrue($delivery->hasValidRoutingProof());
        self::assertSame('IdentityAccess', $delivery->deliveryMessage->sourceModule->value);
        self::assertSame('AccountStatus', $delivery->deliveryMessage->aggregateType->value);
        self::assertSame(
            'identity_access.account_status.lifecycle_facts',
            $delivery->destination->value,
        );
        self::assertInstanceOf(
            AccountStatusDeliveryPayload::class,
            $delivery->deliveryMessage->payload,
        );
        self::assertNotSame(
            $delivery->deliveryMessage->payload->event->eventId->value,
            $delivery->deliveryMessage->messageId->value,
        );
        self::assertNotSame(
            $delivery->deliveryMessage->idempotencyKey->value,
            $delivery->deliveryMessage->messageId->value,
        );
    }

    public function test_non_applied_result_never_creates_an_outbox_record(): void
    {
        $writer = new CapturingAccountStatusRoutedWriter;
        $result = $this->integration(
            new FixedAccountStatusOrchestrator(AccountStatusOrchestrationStatus::AlreadyInState),
            $writer,
            new ImmediateAccountStatusAtomicTransaction,
        )->transition($this->request());

        self::assertSame(AccountStatusOrchestrationStatus::AlreadyInState, $result->status);
        self::assertSame([], $writer->deliveries);
    }

    public function test_outbox_rejection_is_closed_as_persistence_corruption(): void
    {
        $writer = new CapturingAccountStatusRoutedWriter(
            PublicProjectionOutboxWriteResult::DivergentMessage,
        );
        $result = $this->integration(
            new FixedAccountStatusOrchestrator(AccountStatusOrchestrationStatus::Applied),
            $writer,
            new ImmediateAccountStatusAtomicTransaction,
        )->transition($this->request());

        self::assertSame(AccountStatusOrchestrationStatus::PersistenceCorrupted, $result->status);
        self::assertNull($result->state);
    }

    private function integration(
        AccountStatusOrchestrator $orchestrator,
        PublicProjectionOutboxRoutedWriterV1 $writer,
        AccountStatusAtomicTransaction $transaction,
    ): AccountStatusAtomicEventOrchestrator {
        return new AccountStatusAtomicEventOrchestrator(
            $orchestrator,
            $transaction,
            new AccountStatusEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(
                new PublicProjectionDeliveryEventCatalog,
            ),
            new DeterministicAccountStatusEventRouter(
                new AccountStatusTransportSerializer,
            ),
            $writer,
            PublicProjectionOutboxConsumerId::fromString('account-status-atomic'),
        );
    }

    private function request(): AccountStatusAtomicEventRequest
    {
        return new AccountStatusAtomicEventRequest(
            new AccountStatusContextV1(
                AccountId::fromString('49f00000-0000-4000-8000-000000000002'),
                AccountStatusState::Active,
                new AccountStatusVersion(0),
                new AccountStatusVersion(0),
                AccountStatusAction::Suspend,
                new AccountStatusActorId('operator-49k'),
                new AccountStatusOccurredAt(
                    new DateTimeImmutable('2026-07-26T17:00:00+00:00'),
                ),
                new AccountStatusIntentId('intent-49k'),
            ),
            new DateTimeImmutable('2026-07-26T17:00:01+00:00'),
        );
    }
}

final readonly class FixedAccountStatusOrchestrator implements AccountStatusOrchestrator
{
    public function __construct(
        private AccountStatusOrchestrationStatus $status,
    ) {}

    public function transition(AccountStatusContextV1 $context): AccountStatusOrchestrationResult
    {
        return new AccountStatusOrchestrationResult(
            $this->status,
            $this->status === AccountStatusOrchestrationStatus::Applied
                ? AccountStatusState::Suspended
                : $context->currentState,
        );
    }
}

final class ImmediateAccountStatusAtomicTransaction implements AccountStatusAtomicTransaction
{
    public int $calls = 0;

    public function run(Closure $lifecycleAndOutboxWrites): mixed
    {
        $this->calls++;

        return $lifecycleAndOutboxWrites();
    }
}

final class CapturingAccountStatusRoutedWriter implements PublicProjectionOutboxRoutedWriterV1
{
    /** @var list<PublicProjectionRoutedDeliveryMessageV1> */
    public array $deliveries = [];

    public function __construct(
        private readonly PublicProjectionOutboxWriteResult $result = PublicProjectionOutboxWriteResult::Applied,
    ) {}

    public function appendRouted(
        PublicProjectionRoutedDeliveryMessageV1 $delivery,
        PublicProjectionOutboxConsumerId $consumerId,
    ): PublicProjectionOutboxWriteResult {
        $this->deliveries[] = $delivery;

        return $this->result;
    }
}
