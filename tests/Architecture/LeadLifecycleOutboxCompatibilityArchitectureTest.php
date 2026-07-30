<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_catalog_and_mapper_extend_the_single_existing_points(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');

        self::assertSame(1, substr_count($catalog, 'foreach (LeadLifecycleEventType::cases()'));
        self::assertSame(1, substr_count($catalog, "'ContactsLeads', 'LeadLifecycle', LeadLifecycleDeliveryPayload::class"));
        self::assertSame(1, substr_count($mapper, 'LeadLifecycleDeliveryPayload::restore($data)'));
        self::assertStringNotContainsString('new PostgreSqlPublicProjectionOutboxMapper', $catalog);
    }

    public function test_consumer_contains_no_workflow_outbox_or_business_source_logic(): void
    {
        $consumer = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/LeadLifecycleEventConsumption/LeadLifecycleDeliveryConsumer.php');

        self::assertSame(1, substr_count($consumer, '->route('));
        self::assertSame(1, substr_count($consumer, '->consumptionFor('));
        foreach (['Workflow', 'ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'PDO', 'Outbox', 'beginTransaction'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $consumer);
        }
    }

    public function test_worker_registry_has_one_closed_lead_registration_loop(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(LeadLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, '$leadLifecycleConsumer = $app->make(LeadLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (LeadLifecycleEventType::cases()'));
    }

    public function test_frozen_migrations_and_transport_contract_remain_free_of_outbox_compatibility(): void
    {
        $root = dirname(__DIR__, 2);
        $migration025 = (string) file_get_contents($root.'/app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/Migrations/025_lead_lifecycle_event_inbox.sql');
        $migration026 = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/026_contacts_leads_outbox_owner.sql');
        $payload = (string) file_get_contents($root.'/app/Application/LeadLifecycleEventTransport/LeadLifecycleDeliveryPayload.php');

        self::assertStringNotContainsString('public_projection_outbox', $migration025);
        self::assertStringNotContainsString('lead_lifecycle_event_inbox', $migration026);
        self::assertStringNotContainsString('PublicProjectionOutbox', $payload);
    }
}
