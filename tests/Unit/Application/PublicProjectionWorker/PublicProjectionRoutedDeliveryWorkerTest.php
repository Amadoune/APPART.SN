<?php

namespace Tests\Unit\Application\PublicProjectionWorker;

use App\Application\AccountStatusEventConsumption\AccountStatusDeliveryConsumer;
use App\Application\AccountStatusEventRouting\AccountStatusRoutingDestination;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryDestination;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryStatus;
use App\Application\PublicProjectionDelivery\PublicProjectionRoutedDeliveryMessageV1;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryMode;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryOutcome;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\AccountStatusDeliveryTestFactory;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionOutboxRetryPolicy;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxClaimManager;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxReader;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxState;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxWriter;

final class PublicProjectionRoutedDeliveryWorkerTest extends TestCase
{
    public function test_worker_selects_routed_v1_from_registration_without_owner_specific_branch(): void
    {
        $message = AccountStatusDeliveryTestFactory::genericMessage();
        $delivery = PublicProjectionRoutedDeliveryMessageV1::fromDecision(
            $message,
            PublicProjectionDeliveryDestination::fromString(
                AccountStatusRoutingDestination::LifecycleFacts->value,
            ),
        );
        $consumerId = PublicProjectionOutboxConsumerId::fromString('account-status-worker');
        $state = new FakePublicProjectionOutboxState;
        $writer = new FakePublicProjectionOutboxWriter($state);
        $writer->appendRouted($delivery, $consumerId);
        $registry = new PublicProjectionDeliveryConsumerRegistry([
            new PublicProjectionDeliveryConsumerRegistration(
                $consumerId,
                $message->eventType,
                PublicProjectionDeliveryPayloadVersion::fromInt(1),
                new AccountStatusDeliveryConsumer(new AccountStatusTransportSerializer),
                PublicProjectionDeliveryMode::RoutedV1,
            ),
        ]);
        $worker = new PublicProjectionDeliveryWorker(
            new FakePublicProjectionOutboxReader($state),
            new FakePublicProjectionOutboxClaimManager($state),
            $writer,
            $registry,
            new FakePublicProjectionOutboxRetryPolicy,
            new FakePublicProjectionDeliveryClock(new DateTimeImmutable('2026-07-26T16:00:00+00:00')),
            PublicProjectionDeliveryWorkerId::fromString('worker:account-status'),
            10,
            60,
        );

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->claimed);
        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(
            PublicProjectionDeliveryStatus::Delivered,
            $state->find($message, $consumerId)?->status,
        );
        self::assertNotNull($state->find($message, $consumerId)?->routedDelivery);
    }
}
