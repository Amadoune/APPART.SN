<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_workflow_foundation_has_exactly_eight_files_and_no_technical_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(8, $files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'Http', 'Runtime', 'PDO', 'PostgreSql', 'Repository', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Migration'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_workflow_has_no_clock_identity_event_or_external_input(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['DateTime', 'Carbon', 'now(', 'time(', 'UUID', 'Uuid', 'random', 'Event', 'ReservationId', 'Registry'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_mapping_is_closed_and_contains_exactly_eleven_transitions(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle/ReservationLifecycleWorkflow.php');
        self::assertStringNotContainsString('default', $workflow);
        self::assertSame(11, substr_count($workflow, "' => ReservationLifecycleState::"));
        self::assertStringNotContainsString('ListingPublication', $workflow);
        self::assertStringNotContainsString('PropertyLifecycle', $workflow);
    }

    public function test_workflow_slice_remains_isolated_from_later_persistence_and_runtime_layers(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertDirectoryDoesNotExist($root.'/src/Modules/ReservationLifecycle/Domain');
        self::assertSame(
            [
                $root.'/app/Application/ReservationLifecycleEventConsumer',
                $root.'/app/Application/ReservationLifecycleEventIntegration',
                $root.'/app/Application/ReservationLifecycleEventRouting',
                $root.'/app/Application/ReservationLifecycleEventTransport',
                $root.'/app/Http/ReservationLifecycleHttpResultMapper.php',
                $root.'/app/Infrastructure/ReservationLifecycleEventRouting',
            ],
            glob($root.'/app/**/*ReservationLifecycle*') ?: [],
        );
        foreach (glob($root.'/src/Modules/ReservationLifecycle/Application/ReservationLifecycle/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('ReservationLifecyclePersistence', $contents);
            self::assertStringNotContainsString('Infrastructure', $contents);
        }
    }
}
