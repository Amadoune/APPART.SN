<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_is_application_only_and_technology_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/Event';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(10, $files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'PDO', 'PostgreSql', '\\Infrastructure\\', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Http', 'Runtime', 'Repository', 'Aggregate'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_catalog_contains_exactly_twelve_explicit_mappings_without_workflow_consultation(): void
    {
        $catalog = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/Event/PropertyLifecycleEventCatalog.php');
        self::assertSame(12, substr_count($catalog, "' => PropertyLifecycleEventType::"));
        self::assertStringNotContainsString('PropertyLifecycleWorkflow', $catalog);
        self::assertStringNotContainsString('->decide(', $catalog);
        self::assertStringNotContainsString('Initialized', $catalog);
    }

    public function test_metadata_and_identity_have_no_implicit_clock_or_random_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/Event';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['now()', 'new DateTime', 'CURRENT_TIMESTAMP', 'clock_timestamp', 'microtime(', 'time()', 'random', 'uuid'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }
    }

    public function test_contract_itself_contains_no_binding_migration_transport_or_publication(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (glob($root.'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/Event/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('PropertyLifecycleDeliveryPayload', $contents);
            self::assertStringNotContainsString('PropertyLifecycleEventDeliveryConsumer', $contents);
        }
    }
}
