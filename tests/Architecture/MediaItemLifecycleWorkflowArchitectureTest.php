<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_foundation_contains_only_the_seven_contractual_models(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycle';
        $files = glob($root.'/*.php') ?: [];

        self::assertCount(7, $files);
        self::assertSame([
            'MediaItemLifecycleAction.php',
            'MediaItemLifecycleDecision.php',
            'MediaItemLifecycleDiagnostic.php',
            'MediaItemLifecycleState.php',
            'MediaItemLifecycleTransition.php',
            'MediaItemLifecycleWorkflow.php',
            'MediaItemLifecycleWorkflowResult.php',
        ], array_map('basename', $files));
    }

    public function test_workflow_has_no_collection_or_technical_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycle';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Domain\\Model\\MediaCollection', 'Domain\\Model\\MediaItem', 'Domain\\ValueObject\\MediaStatus', 'MediaOrder', 'MediaChecksum', 'Property', 'Illuminate', 'Http', 'Runtime', 'PDO', 'PostgreSql', 'Repository', 'Outbox', 'Worker', 'Consumer', 'DateTime', 'now(', 'UUID', 'random'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_table_is_closed_without_default(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycle/MediaItemLifecycleWorkflow.php');

        self::assertStringNotContainsString('default', $workflow);
        self::assertSame(2, substr_count($workflow, '$this->allowed('));
        self::assertStringContainsString("'removed>remove', 'removed>archive', 'archived>remove', 'archived>archive'", $workflow);
        self::assertStringContainsString("'active>unknown', 'removed>unknown', 'archived>unknown'", $workflow);
    }

    public function test_existing_collection_aggregate_remains_independent(): void
    {
        $aggregate = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Domain/Model/MediaCollection.php');
        self::assertStringNotContainsString('MediaItemLifecycle', $aggregate);
    }
}
