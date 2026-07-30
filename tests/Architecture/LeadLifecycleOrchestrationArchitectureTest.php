<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestrator_has_only_certified_dependencies(): void
    {
        $file = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/LeadLifecycleOrchestration/DeterministicLeadLifecycleOrchestrator.php');
        foreach (['ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'PDO', 'PostgreSql', 'Http', 'Event', 'Outbox', 'Worker', 'Consumer', 'now()', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $file);
        }
        self::assertStringContainsString('LeadLifecycleContextualReplayInspector', $file);
        self::assertStringContainsString('LeadLifecycleContextualTransitionStore', $file);
        self::assertStringContainsString('LeadLifecycleWorkflow', $file);
    }
}
