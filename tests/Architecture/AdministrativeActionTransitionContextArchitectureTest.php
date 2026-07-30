<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionTransitionContextArchitectureTest extends TestCase
{
    public function test_contract_is_application_only_and_has_no_infrastructure_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionTransitionContext';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            foreach (['Illuminate\\', 'PDO', 'PostgreSql', 'Repository', 'Outbox', 'Inbox', 'Http', 'Event', 'Worker'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getFilename());
            }
        }
    }

    public function test_replay_policy_neither_calls_workflow_nor_reconstructs_transition_or_context(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionTransitionContext/AdministrativeActionReplayPolicy.php',
        );
        foreach (['AdministrativeActionLifecycleWorkflow', '->decide(', 'new AdministrativeActionLifecycleTransition', 'new AdministrativeActionDecisionContext', 'match (', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('$inspected->transition->action', $source);
        self::assertStringContainsString('$inspected->checksum->value', $source);
    }

    public function test_certified_foundations_and_runtime_composition_remain_unchanged_by_the_contract(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertStringContainsString('alias(PostgreSqlAdministrativeActionContextualReplayInspector::class, AdministrativeActionContextualReplayInspector::class)', $provider);
        self::assertSame([], glob($root.'/database/migrations/*035*') ?: []);
        self::assertSame([], glob($root.'/database/migrations/postgresql/*035*') ?: []);
        self::assertSame([], glob($root.'/src/Modules/AdministrationAudit/Infrastructure/**/*Contextual*') ?: []);
    }
}
