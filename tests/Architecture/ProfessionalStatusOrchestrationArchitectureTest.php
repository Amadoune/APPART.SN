<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestrator_is_a_pure_application_coordinator(): void
    {
        $file = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusOrchestration/DeterministicProfessionalStatusOrchestrator.php');
        self::assertSame(1, substr_count($file, '->decide('));
        self::assertSame(1, substr_count($file, '->append('));
        self::assertSame(1, substr_count($file, '->inspectLatest('));
        foreach (['PostgreSql', 'PDO', 'Laravel', 'Illuminate', 'Http', 'Event', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Eligibility', 'now(', 'random', 'new ProfessionalStatusTransition'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $file);
        }
    }

    public function test_runtime_composition_is_unique_lazy_and_uses_no_parallel_provider(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        foreach (['singleton(PostgreSqlProfessionalStatusContextualTransitionRepository::class)', 'alias(PostgreSqlProfessionalStatusContextualTransitionRepository::class, ProfessionalStatusContextualTransitionStore::class)', 'singleton(PostgreSqlProfessionalStatusContextualReplayInspector::class)', 'alias(PostgreSqlProfessionalStatusContextualReplayInspector::class, ProfessionalStatusContextualReplayInspector::class)', 'singleton(DeterministicProfessionalStatusOrchestrator::class)', 'alias(DeterministicProfessionalStatusOrchestrator::class, ProfessionalStatusOrchestrator::class)'] as $binding) {
            self::assertSame(1, substr_count($provider, $binding));
        }
        self::assertSame([], glob($root.'/app/Providers/*ProfessionalStatus*') ?: []);
        foreach (['->execute(', '->decide(', '->append(', '->inspectLatest(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }
}
