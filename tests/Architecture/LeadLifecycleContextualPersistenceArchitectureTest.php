<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleContextualPersistenceArchitectureTest extends TestCase
{
    public function test_addition_is_isolated_and_runtime_free(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/PostgreSqlLeadLifecycleContextualTransitionRepository.php');
        foreach (['ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'Http', 'Outbox', 'Event', 'Worker', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlLeadLifecycleContextualTransitionRepository::class)'));
    }

    public function test_migration_is_additive_and_historical_generation_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $up = (string) file_get_contents($root.'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/024_lead_lifecycle_context.sql');
        self::assertStringContainsString('lead_lifecycle_transition_contexts', $up);
        self::assertStringNotContainsString('REFERENCES contacts_leads.lead_lifecycle_transitions', $up);
        self::assertStringNotContainsString('ALTER TABLE contacts_leads.lead_lifecycle_transitions', $up);
    }
}
