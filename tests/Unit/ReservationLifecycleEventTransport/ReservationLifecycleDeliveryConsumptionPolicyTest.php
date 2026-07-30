<?php

namespace Tests\Unit\ReservationLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumptionPolicy;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReservationLifecycleDeliveryConsumptionPolicyTest extends TestCase
{
    #[DataProvider('closedMatrix')]
    public function test_closed_routing_result_is_mapped_to_the_certified_consumption_result(ReservationLifecycleRoutingStatus $routing, PublicProjectionDeliveryConsumptionResult $consumption): void
    {
        $result = (new ReservationLifecycleDeliveryConsumptionPolicy)->consumptionFor(new ReservationLifecycleRoutingResult($routing));

        self::assertSame($consumption, $result);
    }

    /** @return iterable<string, array{ReservationLifecycleRoutingStatus,PublicProjectionDeliveryConsumptionResult}> */
    public static function closedMatrix(): iterable
    {
        yield 'stored acknowledges normally' => [ReservationLifecycleRoutingStatus::Stored, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'already stored acknowledges idempotently' => [ReservationLifecycleRoutingStatus::AlreadyStored, PublicProjectionDeliveryConsumptionResult::AlreadyConsumed];
        yield 'corrupted envelope quarantines divergence' => [ReservationLifecycleRoutingStatus::CorruptedEnvelope, PublicProjectionDeliveryConsumptionResult::DivergentPayload];
        yield 'persistence corruption retries' => [ReservationLifecycleRoutingStatus::PersistenceCorrupted, PublicProjectionDeliveryConsumptionResult::RetryableFailure];
    }

    public function test_policy_is_final_immutable_and_exhaustive(): void
    {
        $reflection = new ReflectionClass(ReservationLifecycleDeliveryConsumptionPolicy::class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertCount(4, ReservationLifecycleRoutingStatus::cases());
    }
}
