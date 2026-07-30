<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationWorkflowArchitectureTest extends TestCase
{
    public function test_workflow_is_deterministic_application_only_and_technology_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'PDO', 'PostgreSql', '\\Infrastructure\\', 'Repository', 'Registry', 'Aggregate', 'Http', 'Runtime', 'Projection', 'Outbox', 'PublicListingQuery', 'DateTime', 'now()', 'CURRENT_TIMESTAMP', 'clock_timestamp'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents, $file);
        }
    }

    public function test_workflow_does_not_publish_events_or_mutate_certified_domain(): void
    {
        $workflow = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/ListingPublicationWorkflow.php');
        self::assertIsString($workflow);
        foreach (['dispatch', 'event(', 'save(', 'store(', 'write(', 'publish(', 'Listing::', 'ListingStatus'] as $forbidden) {
            self::assertStringNotContainsStringIgnoringCase($forbidden, $workflow);
        }
    }

    public function test_no_sprint_specific_infrastructure_runtime_or_http_artifact_exists(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['app/Infrastructure', 'app/Http', 'app/Providers', 'database', 'routes', 'src/Modules/ListingLifecycle/Infrastructure'] as $directory) {
            self::assertSame([], glob($root.'/'.$directory.'/*ListingPublicationWorkflow*') ?: [], $directory);
        }
    }
}
