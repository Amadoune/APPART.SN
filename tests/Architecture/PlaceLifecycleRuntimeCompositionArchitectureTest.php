<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_bindings_and_health_registrations_are_unique(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );

        self::assertSame(1, substr_count($provider, 'singleton(PlaceLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PlaceLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlPlaceLifecycleWorkflowStore::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlPlaceLifecycleWorkflowStore::class, PlaceLifecycleWorkflowStore::class)'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::PlaceLifecycleWorkflow, PlaceLifecycleWorkflow::class'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::PlaceLifecycleWorkflowStore, PlaceLifecycleWorkflowStore::class'));
    }

    public function test_place_composition_remains_declarative_after_atomic_integration(): void
    {
        $provider = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );
        $lines = array_values(array_filter(
            explode("\n", $provider),
            static fn (string $line): bool => str_contains($line, 'PlaceLifecycle'),
        ));
        $placeComposition = implode("\n", $lines);

        foreach (['Controller', 'Http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $placeComposition);
        }

        self::assertStringNotContainsString('->initialize(', $placeComposition);
        self::assertStringNotContainsString('->append(', $placeComposition);
        self::assertStringNotContainsString('->read(', $placeComposition);
        self::assertStringNotContainsString('beginTransaction(', $placeComposition);
    }

    public function test_runtime_health_catalog_preserves_the_fifty_five_place_baseline(): void
    {
        $component = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertGreaterThanOrEqual(55, substr_count($component, 'case '));
        self::assertGreaterThanOrEqual(55, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertSame(1, substr_count($component, "case PlaceLifecycleWorkflow = 'place_lifecycle_workflow';"));
        self::assertSame(1, substr_count($component, "case PlaceLifecycleWorkflowStore = 'place_lifecycle_workflow_store';"));
        self::assertSame(1, substr_count($component, "case PlaceLifecycleInboxStore = 'place_lifecycle_inbox_store';"));
        self::assertSame(1, substr_count($component, "case PlaceLifecycleEventRouter = 'place_lifecycle_event_router';"));
        self::assertSame(1, substr_count($component, "case PlaceLifecycleDeliveryConsumer = 'place_lifecycle_delivery_consumer';"));
    }

    public function test_migration_038_and_certified_foundations_are_not_amended_by_composition(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/038_place_lifecycle_workflow.sql');
        $workflow = (string) file_get_contents($root.'/src/Modules/Geography/Application/PlaceLifecycle/PlaceLifecycleWorkflow.php');
        $context = (string) file_get_contents($root.'/src/Modules/Geography/Application/PlaceMergeContext/PlaceMergeContextV1.php');

        self::assertStringNotContainsString('Runtime', $migration);
        self::assertStringNotContainsString('Runtime', $workflow);
        self::assertStringNotContainsString('Runtime', $context);
    }
}
