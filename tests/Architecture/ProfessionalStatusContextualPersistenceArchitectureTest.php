<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusContextualPersistenceArchitectureTest extends TestCase
{
    public function test_contextual_persistence_is_additive_and_runtime_free(): void
    {
        $root = dirname(__DIR__, 2);
        $historical = (string) file_get_contents($root.'/src/Modules/Professionals/Application/ProfessionalStatusPersistence/Contract/ProfessionalStatusWorkflowStore.php');
        $migration027 = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.sql');
        $migration028 = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/028_professional_status_context.sql');

        self::assertStringNotContainsString('Context', $historical);
        self::assertStringNotContainsString('actor', strtolower($migration027));
        self::assertStringContainsString('professional_status_transition_contexts', $migration028);
        self::assertStringNotContainsString('FOREIGN KEY', strtoupper($migration028));
    }

    public function test_repository_contains_no_workflow_or_forbidden_delivery_dependency(): void
    {
        $repository = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/PostgreSqlProfessionalStatusContextualTransitionRepository.php');

        foreach (['new ProfessionalStatusWorkflow', '->decide(', 'Http', 'Event', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Eligibility'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
    }
}
