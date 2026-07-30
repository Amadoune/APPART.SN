<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecyclePostgreSqlArchitectureTest extends TestCase
{
    public function test_persistence_has_no_runtime_http_outbox_or_eligibility_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads';
        $files = array_merge(
            glob($root.'/Application/LeadLifecyclePersistence/**/*.php') ?: [],
            glob($root.'/Application/LeadLifecyclePersistence/*.php') ?: [],
            glob($root.'/Infrastructure/Persistence/**/*.php') ?: [],
            glob($root.'/Infrastructure/Persistence/*.php') ?: [],
        );
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', '\\Http\\', '\\Runtime\\', 'Outbox', 'Worker', 'Consumer', 'Projection', 'ListingCatalog', 'AdvertiserCatalog'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_repository_contains_no_workflow_or_transition_matrix(): void
    {
        $repository = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/PostgreSqlLeadLifecycleWorkflowRepository.php');
        self::assertStringNotContainsString('new LeadLifecycleWorkflow', $repository);
        self::assertStringNotContainsString('->decide(', $repository);
        self::assertStringNotContainsString("'created'", $repository);
        self::assertStringNotContainsString("'delivered'", $repository);
        self::assertStringNotContainsString("'rejected'", $repository);
        self::assertStringNotContainsString("'closed'", $repository);
        self::assertStringNotContainsString('default', $repository);
    }

    public function test_migration_contains_exactly_the_four_certified_transitions(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/022_lead_lifecycle_workflow.sql');
        self::assertSame(4, preg_match_all("/\\('(?:created|delivered|rejected)','(?:deliver|reject|close)','(?:delivered|rejected|closed)'\\)/", $migration));
        self::assertStringContainsString('contacts_leads.lead_lifecycle_transitions', $migration);
        self::assertStringNotContainsString('timestamp', strtolower($migration));
    }

    public function test_existing_lead_aggregate_remains_decoupled(): void
    {
        $aggregate = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Domain/Model/Lead.php');
        self::assertStringNotContainsString('LeadLifecyclePersistence', $aggregate);
    }
}
