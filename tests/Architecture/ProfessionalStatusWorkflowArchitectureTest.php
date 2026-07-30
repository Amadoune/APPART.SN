<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusWorkflowArchitectureTest extends TestCase
{
    public function test_foundation_contains_only_the_seven_contractual_models(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusLifecycle';
        $files = glob($root.'/*.php') ?: [];

        self::assertCount(7, $files);
        self::assertSame([
            'ProfessionalStatusAction.php',
            'ProfessionalStatusDecision.php',
            'ProfessionalStatusDiagnostic.php',
            'ProfessionalStatusState.php',
            'ProfessionalStatusTransition.php',
            'ProfessionalStatusWorkflow.php',
            'ProfessionalStatusWorkflowResult.php',
        ], array_map('basename', $files));
    }

    public function test_foundation_has_no_aggregate_or_technical_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusLifecycle';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Domain\\Model\\Professional', 'Establishment', 'Mandate', 'Eligibility', 'Illuminate', 'Laravel', 'Http', 'Runtime', 'PDO', 'PostgreSql', 'Repository', 'Outbox', 'Worker', 'Consumer', 'Projection', 'Migration', 'DateTime', 'Carbon', 'now(', 'time(', 'UUID', 'Uuid', 'random'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_table_is_closed_without_default(): void
    {
        $workflow = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Application/ProfessionalStatusLifecycle/ProfessionalStatusWorkflow.php');

        self::assertStringNotContainsString('default', $workflow);
        self::assertSame(2, substr_count($workflow, '$this->allowed('));
        self::assertStringContainsString("'active>reactivate', 'suspended>suspend'", $workflow);
        self::assertStringContainsString("'active>unknown', 'suspended>unknown'", $workflow);
    }

    public function test_existing_professional_aggregate_remains_independent(): void
    {
        $aggregate = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Professionals/Domain/Model/Professional.php');
        self::assertStringNotContainsString('ProfessionalStatusLifecycle', $aggregate);
    }
}
