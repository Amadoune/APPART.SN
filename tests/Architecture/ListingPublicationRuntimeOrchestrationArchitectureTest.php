<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationRuntimeOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestrator_delegates_to_the_certified_workflow_and_store_only(): void
    {
        $file = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/DeterministicListingPublicationOrchestrator.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('$this->store->read(', $contents);
        self::assertStringContainsString('$this->workflow->decide(', $contents);
        self::assertStringContainsString('$this->store->append(', $contents);
        self::assertSame(1, substr_count($contents, '$this->workflow->decide('));
        self::assertSame(1, substr_count($contents, '$this->store->append('));
        foreach (['new ListingPublicationTransition', 'TRANSITIONS', 'ListingPublicationState::', 'ListingPublicationAction::'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_orchestration_has_no_technical_delivery_or_domain_aggregate_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Laravel', 'Http', 'Outbox', 'Projection', 'Worker', 'Consumer', 'Aggregate', 'PDO', 'PostgreSql'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_runtime_root_contains_one_lazy_orchestrator_alias_and_no_execution(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicListingPublicationOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicListingPublicationOrchestrator::class, ListingPublicationOrchestrator::class)'));
        foreach (['->transition(', '->decide(', '->append(', '->read('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_orchestration_foundation_contains_no_provider_outbox_or_projection_artifact(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame([], glob($root.'/app/Providers/*ListingPublication*') ?: []);
        self::assertSame([], glob($root.'/app/**/*ListingPublication*Outbox*') ?: []);
        self::assertSame([], glob($root.'/app/**/*ListingPublication*Projection*') ?: []);
        foreach (glob($root.'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/*.php') ?: [] as $file) {
            self::assertStringNotContainsString('App\\Http', (string) file_get_contents($file));
        }
    }
}
