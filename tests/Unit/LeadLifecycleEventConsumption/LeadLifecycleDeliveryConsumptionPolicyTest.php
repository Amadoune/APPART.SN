<?php

namespace Tests\Unit\LeadLifecycleEventConsumption;

use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumptionPolicy;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LeadLifecycleDeliveryConsumptionPolicyTest extends TestCase
{
    #[DataProvider('closedMatrix')]
    public function test_closed_routing_result_is_mapped_without_implicit_decision(
        LeadLifecycleEventRoutingResult $routing,
        PublicProjectionDeliveryConsumptionResult $expected,
    ): void {
        self::assertSame($expected, (new LeadLifecycleDeliveryConsumptionPolicy)->consumptionFor($routing));
    }

    /** @return iterable<string, array{LeadLifecycleEventRoutingResult, PublicProjectionDeliveryConsumptionResult}> */
    public static function closedMatrix(): iterable
    {
        yield 'routed acknowledges' => [LeadLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred blocks readiness' => [LeadLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable stays retryable' => [LeadLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported is explicit' => [LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corruption is divergent' => [LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    public function test_policy_is_final_immutable_and_has_no_execution_dependency(): void
    {
        $reflection = new ReflectionClass(LeadLifecycleDeliveryConsumptionPolicy::class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertSame([], $reflection->getConstructor()?->getParameters() ?? []);
    }
}
