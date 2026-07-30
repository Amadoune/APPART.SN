<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusTransitionContextArchitectureTest extends TestCase
{
    public function test_foundation_is_application_only_and_has_no_concrete_inspector(): void
    {
        $root = dirname(__DIR__, 2);
        $directory = $root.'/src/Modules/Professionals/Application/ProfessionalStatusTransitionContext';
        $files = glob($directory.'/*.php') ?: [];
        $files[] = $directory.'/Contract/ProfessionalStatusContextualReplayInspector.php';
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach (['PostgreSql', 'PDO', 'Laravel', 'Illuminate', 'Repository', 'Migration', 'Outbox', 'Inbox', 'Consumer', 'Worker', 'Http', 'Event', 'Eligibility', 'now(', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }

    }

    public function test_certified_workflow_store_runtime_and_migration_are_not_amended(): void
    {
        $root = dirname(__DIR__, 2);
        $store = (string) file_get_contents($root.'/src/Modules/Professionals/Application/ProfessionalStatusPersistence/Contract/ProfessionalStatusWorkflowStore.php');
        $migration = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.sql');

        self::assertStringNotContainsString('Context', $store);
        self::assertStringNotContainsString('actor', strtolower($migration));
        self::assertStringNotContainsString('occurred_at', strtolower($migration));
    }

    public function test_replay_policy_never_calls_or_reconstructs_the_workflow(): void
    {
        $policy = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusTransitionContext/ProfessionalStatusReplayPolicy.php');

        foreach (['ProfessionalStatusWorkflow', '->decide(', 'new ProfessionalStatusTransition', 'match (', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $policy);
        }
    }
}
