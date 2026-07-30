<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleWorkflowArchitectureTest extends TestCase
{
    public function test_workflow_foundation_contains_only_the_seven_contractual_models(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadLifecycle';
        $files = glob($root.'/*.php') ?: [];

        self::assertCount(7, $files);
        self::assertSame(
            [
                'LeadLifecycleAction.php',
                'LeadLifecycleDecision.php',
                'LeadLifecycleDiagnostic.php',
                'LeadLifecycleState.php',
                'LeadLifecycleTransition.php',
                'LeadLifecycleWorkflow.php',
                'LeadLifecycleWorkflowResult.php',
            ],
            array_map('basename', $files),
        );
    }

    public function test_workflow_has_no_technical_or_eligibility_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadLifecycle';
        foreach (glob($root.'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach ([
                'Illuminate', 'Laravel', 'Http', 'Runtime', 'PDO', 'PostgreSql', 'Repository',
                'Outbox', 'Worker', 'Consumer', 'Projection', 'Migration', 'ListingCatalog',
                'AdvertiserCatalog', 'LeadRegistry', 'DateTime', 'Carbon', 'now(', 'time(',
                'UUID', 'Uuid', 'random', 'Domain\\Model\\Lead',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_decision_table_is_closed_and_has_no_default_branch(): void
    {
        $workflow = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadLifecycle/LeadLifecycleWorkflow.php',
        );

        self::assertStringNotContainsString('default', $workflow);
        self::assertSame(3, substr_count($workflow, '$this->allowed('));
        self::assertStringContainsString("'delivered>close', 'rejected>close'", $workflow);
        self::assertStringNotContainsString('ListingPublication', $workflow);
        self::assertStringNotContainsString('PropertyLifecycle', $workflow);
        self::assertStringNotContainsString('ReservationLifecycle', $workflow);
    }

    public function test_existing_lead_aggregate_does_not_depend_on_the_workflow_foundation(): void
    {
        $aggregate = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Domain/Model/Lead.php',
        );

        self::assertStringNotContainsString('LeadLifecycle', $aggregate);
    }
}
