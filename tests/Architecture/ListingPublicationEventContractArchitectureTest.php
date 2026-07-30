<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_is_application_only_and_technology_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/Event';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(10, $files);
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'PDO', 'PostgreSql', '\\Infrastructure\\', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Http', 'Runtime', 'Repository', 'Aggregate'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_event_metadata_has_no_implicit_time_source(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/Event';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['now()', 'new DateTime', 'CURRENT_TIMESTAMP', 'clock_timestamp', 'microtime(', 'time()'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }
    }

    public function test_contract_does_not_modify_runtime_http_or_certified_event_infrastructure(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['app/Providers', 'app/Http', 'app/Infrastructure/PublicProjectionOutbox', 'app/Application/PublicProjectionWorker'] as $directory) {
            foreach (glob($root.'/'.$directory.'/*ListingPublicationEvent*') ?: [] as $file) {
                self::fail('Unexpected technical Listing Publication event artifact: '.$file);
            }
        }
        self::addToAssertionCount(4);
    }

    public function test_catalog_contains_exactly_twenty_seven_explicit_mappings(): void
    {
        $catalog = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/Event/ListingPublicationEventCatalog.php');
        self::assertIsString($catalog);
        self::assertSame(27, substr_count($catalog, "' => ListingPublicationEventType::"));
        self::assertStringNotContainsString('ListingPublicationWorkflow', $catalog);
        self::assertStringNotContainsString('->decide(', $catalog);
    }
}
