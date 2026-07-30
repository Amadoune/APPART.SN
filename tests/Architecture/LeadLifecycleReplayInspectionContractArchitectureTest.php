<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleReplayInspectionContractArchitectureTest extends TestCase
{
    public function test_amendment_is_contract_only_and_additive(): void
    {
        $root = dirname(__DIR__, 2);
        $port = (string) file_get_contents($root.'/src/Modules/ContactsLeads/Application/LeadLifecycleTransitionContract/Contract/LeadLifecycleContextualReplayInspector.php');
        foreach (['Infrastructure', 'PostgreSql', 'PDO', 'Laravel', 'ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'Http', 'Outbox', 'Event', 'Worker', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $port);
        }
        $store = (string) file_get_contents($root.'/src/Modules/ContactsLeads/Application/LeadLifecycleTransitionContract/Contract/LeadLifecycleContextualTransitionStore.php');
        self::assertStringNotContainsString('inspectLatest', $store);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlLeadLifecycleContextualReplayInspector::class, LeadLifecycleContextualReplayInspector::class)'));
    }
}
