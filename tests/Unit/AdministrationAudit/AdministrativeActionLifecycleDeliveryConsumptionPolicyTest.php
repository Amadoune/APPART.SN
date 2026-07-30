<?php

namespace Tests\Unit\AdministrationAudit;

use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumptionPolicy;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AdministrativeActionLifecycleDeliveryConsumptionPolicyTest extends TestCase
{
    #[DataProvider('closedMatrix')]
    public function test_closed_routing_result_is_mapped_without_implicit_decision(
        AdministrativeActionLifecycleEventRoutingResult $routing,
        PublicProjectionDeliveryConsumptionResult $expected,
    ): void {
        self::assertSame($expected, (new AdministrativeActionLifecycleDeliveryConsumptionPolicy)->consumptionFor($routing));
    }

    /** @return iterable<string, array{AdministrativeActionLifecycleEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function closedMatrix(): iterable
    {
        yield 'routed acknowledges' => [AdministrativeActionLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred blocks readiness' => [AdministrativeActionLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable stays retryable' => [AdministrativeActionLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported is explicit' => [AdministrativeActionLifecycleEventRoutingResult::rejected(AdministrativeActionLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corruption is divergent' => [AdministrativeActionLifecycleEventRoutingResult::rejected(AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    public function test_policy_is_final_immutable_and_dependency_free(): void
    {
        $reflection = new ReflectionClass(AdministrativeActionLifecycleDeliveryConsumptionPolicy::class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertSame([], $reflection->getConstructor()?->getParameters() ?? []);
    }
}
