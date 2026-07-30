<?php

namespace Tests\Unit\ReservationLifecycleEventRouting;

use App\Application\ReservationLifecycleEventRouting\DeterministicReservationLifecycleEventRouter;
use App\Application\ReservationLifecycleEventRouting\ReservationLifecycleInboxStore;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class DeterministicReservationLifecycleEventRouterTest extends TestCase
{
    #[DataProvider('persistenceOutcomes')]
    public function test_store_result_is_propagated_without_interpretation(ReservationLifecycleRoutingStatus $status): void
    {
        $store = new ReservationInboxStoreStub(new ReservationLifecycleRoutingResult($status));
        $envelope = $this->envelope();
        $result = (new DeterministicReservationLifecycleEventRouter($store))->route($envelope);

        self::assertSame($status, $result->status);
        self::assertSame($envelope, $store->received);
        self::assertSame(1, $store->calls);
    }

    /** @return iterable<string, array{ReservationLifecycleRoutingStatus}> */
    public static function persistenceOutcomes(): iterable
    {
        yield 'stored' => [ReservationLifecycleRoutingStatus::Stored];
        yield 'already stored' => [ReservationLifecycleRoutingStatus::AlreadyStored];
        yield 'corrupted envelope' => [ReservationLifecycleRoutingStatus::CorruptedEnvelope];
        yield 'persistence corrupted' => [ReservationLifecycleRoutingStatus::PersistenceCorrupted];
    }

    public function test_corrupted_envelope_is_rejected_before_the_store_is_called(): void
    {
        $store = new ReservationInboxStoreStub(new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::Stored));
        $result = (new DeterministicReservationLifecycleEventRouter($store))->route($this->corruptedEnvelope());

        self::assertSame(ReservationLifecycleRoutingStatus::CorruptedEnvelope, $result->status);
        self::assertSame(0, $store->calls);
        self::assertNull($store->received);
    }

    public function test_technical_store_exception_is_absorbed_as_persistence_corrupted(): void
    {
        $store = new ReservationInboxStoreStub(new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::Stored), true);
        $result = (new DeterministicReservationLifecycleEventRouter($store))->route($this->envelope());

        self::assertSame(ReservationLifecycleRoutingStatus::PersistenceCorrupted, $result->status);
        self::assertSame(1, $store->calls);
    }

    private function corruptedEnvelope(): ReservationLifecycleTransportEnvelope
    {
        $valid = $this->envelope();
        $reflection = new ReflectionClass(ReservationLifecycleTransportEnvelope::class);
        $corrupted = $reflection->newInstanceWithoutConstructor();
        foreach (['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'] as $property) {
            $reflection->getProperty($property)->setValue(
                $corrupted,
                $property === 'messageId' ? 'reservation-lifecycle-delivery-'.str_repeat('0', 64) : $valid->{$property},
            );
        }

        return $corrupted;
    }

    private function envelope(): ReservationLifecycleTransportEnvelope
    {
        $event = (new ReservationLifecycleEventCatalog)->eventFor(
            ReservationId::fromString('22222222-2222-4222-8222-222222222222'),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            2,
        );

        return ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($event));
    }
}

final class ReservationInboxStoreStub implements ReservationLifecycleInboxStore
{
    public int $calls = 0;

    public ?ReservationLifecycleTransportEnvelope $received = null;

    public function __construct(
        private readonly ReservationLifecycleRoutingResult $result,
        private readonly bool $throw = false,
    ) {}

    public function store(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult
    {
        $this->calls++;
        $this->received = $envelope;
        if ($this->throw) {
            throw new RuntimeException('Simulated technical persistence failure.');
        }

        return $this->result;
    }
}
