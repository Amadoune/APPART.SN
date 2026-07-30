<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleRuntimeOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestration_depends_only_on_the_certified_layers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Geography/Application/PlaceLifecycleOrchestration';
        $orchestrator = (string) file_get_contents($root.'/PlaceLifecycleOrchestrator.php');

        foreach ([
            'PlaceMergeContextInspector',
            'PlaceMergeReplayClassifier',
            'PlaceLifecycleWorkflow',
            'PlaceLifecycleWorkflowStore',
        ] as $dependency) {
            self::assertStringContainsString($dependency, $orchestrator);
        }

        foreach ([
            'PDO',
            'PostgreSql',
            'Illuminate',
            'Laravel',
            'Repository',
            'Event',
            'Transport',
            'Routing',
            'Outbox',
            'Http',
            'Consumer',
            'Worker',
            'now()',
            'random',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $orchestrator);
        }
    }

    public function test_orchestration_contract_is_closed_and_is_composed_only_after_4_8_k(): void
    {
        $root = dirname(__DIR__, 2);
        $status = (string) file_get_contents(
            $root.'/src/Modules/Geography/Application/PlaceLifecycleOrchestration/PlaceLifecycleOrchestrationStatus.php',
        );
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        foreach ([
            'Applied',
            'AlreadyApplied',
            'WorkflowRefused',
            'InspectionMissing',
            'InspectionCorrupted',
            'ContextDivergence',
            'ReplayConflict',
            'SourceVersionConflict',
            'TargetVersionConflict',
            'StateConflict',
            'TransitionRejected',
        ] as $case) {
            self::assertStringContainsString("case {$case} =", $status);
        }

        self::assertStringContainsString('singleton(PlaceLifecycleOrchestrator::class)', $provider);
        self::assertStringContainsString('PlaceMergeContextInspector::class', $provider);
        self::assertStringContainsString('PlaceMergeReplayClassifier::class', $provider);
    }
}
