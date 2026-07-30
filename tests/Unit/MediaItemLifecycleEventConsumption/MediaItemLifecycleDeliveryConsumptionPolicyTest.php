<?php

namespace Tests\Unit\MediaItemLifecycleEventConsumption;

use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumptionPolicy;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MediaItemLifecycleDeliveryConsumptionPolicyTest extends TestCase
{
    #[DataProvider('closedMatrix')]
    public function test_closed_routing_result_is_mapped_without_implicit_decision(MediaItemLifecycleEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        self::assertSame($expected, (new MediaItemLifecycleDeliveryConsumptionPolicy)->consumptionFor($routing));
    }

    /** @return iterable<string, array{MediaItemLifecycleEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function closedMatrix(): iterable
    {
        yield 'routed acknowledges' => [MediaItemLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred blocks readiness' => [MediaItemLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable stays retryable' => [MediaItemLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported is explicit' => [MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corruption is divergent' => [MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    public function test_policy_is_final_immutable_and_dependency_free(): void
    {
        $reflection = new ReflectionClass(MediaItemLifecycleDeliveryConsumptionPolicy::class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertSame([], $reflection->getConstructor()?->getParameters() ?? []);
    }
}
