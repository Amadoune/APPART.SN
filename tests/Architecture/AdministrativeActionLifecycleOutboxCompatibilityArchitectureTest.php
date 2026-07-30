<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_catalog_and_mapper_extend_the_single_existing_points(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');

        self::assertSame(1, substr_count($catalog, 'foreach (AdministrativeActionLifecycleEventType::cases()'));
        self::assertSame(1, substr_count($catalog, "'AdministrationAudit', 'AdministrativeActionLifecycle', AdministrativeActionLifecycleDeliveryPayload::class"));
        self::assertSame(1, substr_count($mapper, 'AdministrativeActionLifecycleDeliveryPayload::restore($data)'));
    }

    public function test_consumer_contains_no_workflow_outbox_or_business_logic(): void
    {
        $consumer = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Application/AdministrativeActionLifecycleEventConsumption/AdministrativeActionLifecycleDeliveryConsumer.php',
        );

        self::assertSame(1, substr_count($consumer, '->route('));
        self::assertSame(1, substr_count($consumer, '->consumptionFor('));
        foreach (['Workflow', 'PDO', 'Outbox', 'beginTransaction', 'AdministrativeActionLifecycleAction', 'DecisionContext', 'HistoricalReason'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $consumer);
        }
    }

    public function test_worker_registry_has_one_closed_registration_loop(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(AdministrativeActionLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, '$administrativeActionLifecycleConsumer = $app->make(AdministrativeActionLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (AdministrativeActionLifecycleEventType::cases()'));
    }

    public function test_frozen_migrations_and_runtime_health_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertStringNotContainsString('public_projection_outbox', (string) file_get_contents($root.'/app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/036_administrative_action_lifecycle_event_inbox.sql'));
        self::assertStringNotContainsString('AdministrativeActionLifecycleDeliveryPayload', (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/037_administration_audit_outbox_owner.sql'));
        $health = (string) file_get_contents($root.'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        self::assertGreaterThanOrEqual(55, substr_count($health, 'case '));
        self::assertStringContainsString("case PlaceLifecycleWorkflow = 'place_lifecycle_workflow';", $health);
        self::assertStringContainsString("case PlaceLifecycleWorkflowStore = 'place_lifecycle_workflow_store';", $health);
        self::assertStringContainsString("case PlaceLifecycleDeliveryConsumer = 'place_lifecycle_delivery_consumer';", $health);
    }
}
