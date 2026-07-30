<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusPersistenceArchitectureTest extends TestCase
{
    public function test_repository_has_no_workflow_matrix_or_forbidden_dependency(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/PostgreSqlProfessionalStatusWorkflowRepository.php');
        foreach (['new ProfessionalStatusWorkflow', 'ProfessionalStatusWorkflow $', 'Establishment', 'Mandate', 'Eligibility', 'Http', 'Outbox', 'Event', 'Worker', 'Consumer', 'Illuminate'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
        self::assertStringNotContainsString("'active>suspend'", $repository);
        self::assertStringNotContainsString("'suspended>reactivate'", $repository);
    }

    public function test_migration_contains_only_the_two_certified_transitions(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Infrastructure/Persistence/PostgreSql/Migrations/027_professional_status_workflow.sql');
        self::assertSame(1, substr_count($migration, "('active','suspend','suspended')"));
        self::assertSame(1, substr_count($migration, "('suspended','reactivate','active')"));
        foreach (['establishment', 'mandate', 'actor', 'occurred_at', 'outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, strtolower($migration));
        }
    }

    public function test_workflow_foundation_remains_persistence_free(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusLifecycle/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('Persistence', $contents, $file);
            self::assertStringNotContainsString('PostgreSql', $contents, $file);
        }
    }
}
