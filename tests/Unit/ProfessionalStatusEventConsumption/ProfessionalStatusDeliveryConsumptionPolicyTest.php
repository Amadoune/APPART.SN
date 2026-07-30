<?php

namespace Tests\Unit\ProfessionalStatusEventConsumption;

use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumptionPolicy;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProfessionalStatusDeliveryConsumptionPolicyTest extends TestCase
{
    #[DataProvider('closedMatrix')]
    public function test_closed_routing_result_is_mapped_without_implicit_decision(ProfessionalStatusEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        self::assertSame($expected, (new ProfessionalStatusDeliveryConsumptionPolicy)->consumptionFor($routing));
    }

    /** @return iterable<string, array{ProfessionalStatusEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function closedMatrix(): iterable
    {
        yield 'routed acknowledges' => [ProfessionalStatusEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred blocks readiness' => [ProfessionalStatusEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable stays retryable' => [ProfessionalStatusEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported is explicit' => [ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corruption is divergent' => [ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    public function test_policy_is_final_immutable_and_dependency_free(): void
    {
        $reflection = new ReflectionClass(ProfessionalStatusDeliveryConsumptionPolicy::class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        self::assertSame([], $reflection->getConstructor()?->getParameters() ?? []);
    }
}
