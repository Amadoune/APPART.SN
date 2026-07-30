<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleDeliveryConsumptionRuntimeArchitectureTest extends TestCase
{
    public function test_consumption_matrix_is_closed_and_has_no_default(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/LeadLifecycleEventConsumption/LeadLifecycleDeliveryConsumptionPolicy.php');

        self::assertSame(4, substr_count($policy, 'LeadLifecycleEventRoutingStatus::'));
        self::assertSame(5, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::'));
        self::assertStringNotContainsString('default', $policy);
    }

    public function test_runtime_has_one_lazy_binding_per_component_and_executes_nothing(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        foreach ([
            'singleton(LeadLifecycleTransportSerializer::class)',
            'singleton(PostgreSqlLeadLifecycleInboxRepository::class)',
            'alias(PostgreSqlLeadLifecycleInboxRepository::class, LeadLifecycleInboxStore::class)',
            'singleton(DurableLeadLifecycleEventRouter::class)',
            'alias(DurableLeadLifecycleEventRouter::class, LeadLifecycleEventRouter::class)',
            'singleton(LeadLifecycleDeliveryConsumptionPolicy::class)',
        ] as $binding) {
            self::assertSame(1, substr_count($provider, $binding), $binding);
        }
        foreach (['->store(', '->route(', '->consumptionFor(', '->prepare(', '->query(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_extension_is_contract_only(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertStringContainsString('RuntimeHealthComponent::LeadLifecycleInboxStore, LeadLifecycleInboxStore::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::LeadLifecycleEventRouter, LeadLifecycleEventRouter::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlLeadLifecycleInboxRepository', $requirements);
        self::assertStringNotContainsString('DurableLeadLifecycleEventRouter', $requirements);
    }
}
