<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleDeliveryConsumptionRuntimeArchitectureTest extends TestCase
{
    public function test_consumption_policy_contains_the_exact_closed_matrix_without_default(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/ReservationLifecycleEventConsumer/ReservationLifecycleDeliveryConsumptionPolicy.php');

        self::assertSame(4, substr_count($policy, 'ReservationLifecycleRoutingStatus::'));
        self::assertSame(1, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::Consumed'));
        self::assertSame(1, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::AlreadyConsumed'));
        self::assertSame(1, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::DivergentPayload'));
        self::assertSame(1, substr_count($policy, 'PublicProjectionDeliveryConsumptionResult::RetryableFailure'));
        self::assertStringNotContainsString('default', $policy);
    }

    public function test_runtime_contains_one_lazy_alias_for_store_and_router(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(ReservationLifecycleTransportSerializer::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlReservationLifecycleInboxRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlReservationLifecycleInboxRepository::class, ReservationLifecycleInboxStore::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicReservationLifecycleEventRouter::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicReservationLifecycleEventRouter::class, ReservationLifecycleEventRouterPort::class)'));
        foreach (['->store(', '->route(', '->prepare(', '->query(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_extension_is_explicit_and_contract_only(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleInboxStore, ReservationLifecycleInboxStore::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleEventRouter, ReservationLifecycleEventRouterPort::class', $requirements);
        self::assertStringNotContainsString('PostgreSqlReservationLifecycleInboxRepository', $requirements);
        self::assertStringNotContainsString('DeterministicReservationLifecycleEventRouter', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleInboxStore, ReservationLifecycleInboxStore::class', $provider);
        self::assertStringContainsString('RuntimeHealthComponent::ReservationLifecycleEventRouter, ReservationLifecycleEventRouterPort::class', $provider);
    }

    public function test_later_outbox_compatibility_reuses_the_certified_policy_without_changing_its_contract(): void
    {
        $root = dirname(__DIR__, 2);
        $consumer = (string) file_get_contents($root.'/app/Application/ReservationLifecycleEventConsumer/ReservationLifecycleDeliveryConsumer.php');
        $policy = (string) file_get_contents($root.'/app/Application/ReservationLifecycleEventConsumer/ReservationLifecycleDeliveryConsumptionPolicy.php');

        self::assertStringContainsString('ReservationLifecycleDeliveryConsumptionPolicy', $consumer);
        self::assertSame(1, substr_count($consumer, '->consumptionFor('));
        self::assertSame(4, substr_count($policy, 'ReservationLifecycleRoutingStatus::'));
        self::assertStringNotContainsString('default', $policy);
    }
}
