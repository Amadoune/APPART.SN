<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusDeliveryConsumptionRuntimeArchitectureTest extends TestCase
{
    public function test_consumption_matrix_is_closed_and_has_no_default(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/ProfessionalStatusEventConsumption/ProfessionalStatusDeliveryConsumptionPolicy.php');
        self::assertSame(4, substr_count($policy, 'ProfessionalStatusEventRoutingStatus::'));
        self::assertSame(5, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::'));
        self::assertStringNotContainsString('default', $policy);
    }

    public function test_runtime_has_one_lazy_binding_and_executes_nothing(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        foreach (['singleton(ProfessionalStatusTransportSerializer::class)', 'singleton(PostgreSqlProfessionalStatusInboxRepository::class)', 'alias(PostgreSqlProfessionalStatusInboxRepository::class, ProfessionalStatusInboxStore::class)', 'singleton(DurableProfessionalStatusEventRouter::class)', 'alias(DurableProfessionalStatusEventRouter::class, ProfessionalStatusEventRouter::class)', 'singleton(ProfessionalStatusDeliveryConsumptionPolicy::class)'] as $binding) {
            self::assertSame(1, substr_count($provider, $binding), $binding);
        }
        foreach (['->store(', '->route(', '->consumptionFor(', '->prepare(', '->query(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_extension_is_contract_only(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalStatusInboxStore, ProfessionalStatusInboxStore::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalStatusEventRouter, ProfessionalStatusEventRouter::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlProfessionalStatusInboxRepository', $requirements);
        self::assertStringNotContainsString('DurableProfessionalStatusEventRouter', $requirements);
    }
}
