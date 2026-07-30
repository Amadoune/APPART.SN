<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleDeliveryConsumptionRuntimeArchitectureTest extends TestCase
{
    public function test_consumption_matrix_is_closed_and_has_no_default(): void
    {
        $policy = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventConsumption/AdministrativeActionLifecycleDeliveryConsumptionPolicy.php',
        );

        self::assertSame(4, substr_count($policy, 'AdministrativeActionLifecycleEventRoutingStatus::'));
        self::assertSame(5, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::'));
        self::assertStringNotContainsString('default', $policy);
    }

    public function test_runtime_has_one_lazy_binding_and_executes_nothing(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        foreach ([
            'singleton(AdministrativeActionLifecycleTransportSerializer::class)',
            'singleton(PostgreSqlAdministrativeActionLifecycleInboxRepository::class)',
            'alias(PostgreSqlAdministrativeActionLifecycleInboxRepository::class, AdministrativeActionLifecycleInboxStore::class)',
            'singleton(DurableAdministrativeActionLifecycleEventRouter::class)',
            'alias(DurableAdministrativeActionLifecycleEventRouter::class, AdministrativeActionLifecycleEventRouter::class)',
            'singleton(AdministrativeActionLifecycleDeliveryConsumptionPolicy::class)',
        ] as $binding) {
            self::assertSame(1, substr_count($provider, $binding), $binding);
        }

        foreach (['->store(', '->route(', '->consumptionFor(', '->prepare(', '->query(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_extension_is_contract_only(): void
    {
        $requirements = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php',
        );

        self::assertStringContainsString('RuntimeHealthComponent::AdministrativeActionLifecycleInboxStore, AdministrativeActionLifecycleInboxStore::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::AdministrativeActionLifecycleEventRouter, AdministrativeActionLifecycleEventRouter::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlAdministrativeActionLifecycleInboxRepository', $requirements);
        self::assertStringNotContainsString('DurableAdministrativeActionLifecycleEventRouter', $requirements);
    }

    public function test_no_consumer_worker_outbox_or_http_is_introduced(): void
    {
        $policy = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventConsumption/AdministrativeActionLifecycleDeliveryConsumptionPolicy.php',
        );

        foreach (['Consumer', 'Worker', 'Outbox', 'Http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $policy);
        }
    }
}
