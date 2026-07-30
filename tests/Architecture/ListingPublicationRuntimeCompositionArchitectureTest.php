<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_production_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(ListingPublicationWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(ListingPublicationWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlListingPublicationWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlListingPublicationWorkflowRepository::class, ListingPublicationWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*ListingPublication*') ?: []);
    }

    public function test_bootstrap_does_not_execute_workflow_storage_or_sql(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        foreach (['->decide(', '->initialize(', '->append(', '->read('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_only_inspects_application_capabilities(): void
    {
        $requirements = file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertIsString($requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ListingPublicationWorkflow, ListingPublicationWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ListingPublicationWorkflowStore, ListingPublicationWorkflowStore::class', $requirements);
        foreach (['PostgreSqlListingPublicationWorkflowRepository', 'ListingPublicationWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_does_not_introduce_http_outbox_projection_or_aggregate_dependencies(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (glob($root.'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/**/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Http', 'Outbox', 'Projection', 'Aggregate'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }
}
