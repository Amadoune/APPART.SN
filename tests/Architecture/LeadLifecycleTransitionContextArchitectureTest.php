<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleTransitionContextArchitectureTest extends TestCase
{
    public function test_foundation_contains_contracts_only(): void
    {
        $root = dirname(__DIR__, 2);
        $contents = '';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/src/Modules/ContactsLeads/Application/LeadLifecycleTransitionContract')) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents .= file_get_contents($file->getPathname());
            }
        }
        foreach (['PDO', 'PostgreSql', 'Laravel', 'Http', 'Outbox', 'Event', 'Worker', 'Consumer', 'ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'now()', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_frozen_store_and_journal_are_not_amended(): void
    {
        $root = dirname(__DIR__, 2);
        $store = (string) file_get_contents($root.'/src/Modules/ContactsLeads/Application/LeadLifecyclePersistence/Contract/LeadLifecycleWorkflowStore.php');
        $migrationPath = $root.'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/022_lead_lifecycle_workflow.sql';
        self::assertFileExists($migrationPath);
        $migration = (string) file_get_contents($migrationPath);
        self::assertStringNotContainsString('actor', $store);
        self::assertStringNotContainsString('occurredAt', $store);
        self::assertStringNotContainsString('actor_id', $migration);
        self::assertStringNotContainsString('occurred_at', $migration);
    }

    public function test_no_runtime_or_production_implementation_is_introduced(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlLeadLifecycleContextualTransitionRepository::class, LeadLifecycleContextualTransitionStore::class)'));
        self::assertStringNotContainsString('LeadLifecycleTransitionEligibilityPolicy', $provider);
    }
}
