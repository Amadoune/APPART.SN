<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_workflow_is_pure_and_depends_only_on_certified_context_and_domain_values(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceLifecycle';
        $files = glob($root.'/*.php') ?: [];
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach ([
            'Illuminate\\', 'PDO', 'PostgreSql', 'PlaceRegistry', 'Projection',
            'Repository', 'Persistence', 'Migration', 'Runtime', 'Transaction',
            'Event', 'Transport', 'Routing', 'Outbox', 'Inbox', 'Http',
            'Inspector', 'Replay', 'SourceVersionConflict',
            'TargetVersionConflict', 'TargetMissing', 'ReplayConflict',
            'ContextDivergence', 'InspectionCorrupted', 'now(', 'random',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }

        self::assertStringContainsString('PlaceMergeContextV1', $source);
    }

    public function test_workflow_has_no_default_or_open_branch(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceLifecycle/PlaceLifecycleWorkflow.php',
        );

        self::assertStringNotContainsString('default', $source);
        self::assertStringNotContainsString('throw ', $source);
        self::assertStringNotContainsString('catch ', $source);
    }

    public function test_certified_context_and_geography_foundations_are_not_amended_by_workflow(): void
    {
        $root = dirname(__DIR__, 2);
        $context = (string) file_get_contents($root.'/src/Modules/Geography/Application/PlaceMergeContext/PlaceMergeContextV1.php');
        $place = (string) file_get_contents($root.'/src/Modules/Geography/Domain/Model/Place.php');
        $registry = (string) file_get_contents($root.'/src/Modules/Geography/Application/Contract/PlaceRegistry.php');

        self::assertStringNotContainsString('PlaceLifecycle', $context);
        self::assertStringNotContainsString('PlaceLifecycleWorkflow', $place);
        self::assertStringNotContainsString('PlaceLifecycleWorkflow', $registry);
        self::assertSame([], glob($root.'/src/Modules/Geography/Infrastructure/*PlaceLifecycle*') ?: []);
        self::assertSame([], glob($root.'/database/migrations/*place_lifecycle*') ?: []);
    }
}
