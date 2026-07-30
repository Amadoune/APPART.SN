<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecyclePersistenceArchitectureTest extends TestCase
{
    public function test_persistence_has_no_future_foundation_dependency_or_business_decision(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography';
        $files = [
            ...glob($root.'/Application/PlaceLifecyclePersistence/*.php') ?: [],
            ...glob($root.'/Application/PlaceLifecyclePersistence/Contract/*.php') ?: [],
            $root.'/Infrastructure/Persistence/PlaceLifecycleWorkflowMapper.php',
            $root.'/Infrastructure/Persistence/PostgreSql/PostgreSqlPlaceLifecycleWorkflowStore.php',
        ];

        foreach ($files as $file) {
            $source = (string) file_get_contents($file);
            foreach ([
                'Illuminate\\', 'App\\Application\\Runtime', 'Orchestrat', 'Outbox', 'Inbox',
                'Event', 'Transport', 'Routing', 'Consumer', 'Worker', 'Http',
                'PlaceLifecycleWorkflow(', 'SameIdentity', 'TargetDisabled',
                'TargetMerged', 'DifferentType', 'DifferentCountry',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file);
            }
        }
    }

    public function test_migration_is_append_only_and_closed_to_certified_transitions(): void
    {
        $migration = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/038_place_lifecycle_workflow.sql',
        );

        foreach ([
            "('enabled','disable','disabled')",
            "('disabled','enable','enabled')",
            "('enabled','merge','merged')",
            "('disabled','merge','merged')",
        ] as $transition) {
            self::assertSame(1, substr_count($migration, $transition));
        }

        foreach (['update ', 'delete ', 'outbox', 'event', 'http'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtolower($migration));
        }
    }

    public function test_certified_context_and_workflow_are_not_amended_by_persistence(): void
    {
        $root = dirname(__DIR__, 2);
        $context = (string) file_get_contents($root.'/src/Modules/Geography/Application/PlaceMergeContext/PlaceMergeContextV1.php');
        $workflow = (string) file_get_contents($root.'/src/Modules/Geography/Application/PlaceLifecycle/PlaceLifecycleWorkflow.php');

        self::assertStringNotContainsString('Persistence', $context);
        self::assertStringNotContainsString('Persistence', $workflow);
        self::assertSame([], glob($root.'/app/*PlaceLifecycle*') ?: []);
    }
}
