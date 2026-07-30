<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_is_isolated_from_forbidden_capabilities(): void
    {
        $root = dirname(__DIR__, 2);
        $source = (string) file_get_contents($root.'/app/Application/LeadLifecycleEventIntegration/LeadLifecycleAtomicEventOrchestrator.php');

        foreach (['ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'Http\\', 'DeliveryConsumer', 'WorkerRegistry', 'LifecycleInbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('LeadLifecycleContextualReplayInspector', $source);
        self::assertStringNotContainsString('new LeadLifecycleTransition(', $source);
        self::assertStringNotContainsString('now()', strtolower($source));
    }

    public function test_only_the_certified_lead_http_adapter_depends_on_atomic_integrator(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame(2, substr_count((string) file_get_contents($root.'/routes/web.php'), 'LeadLifecycleHttpController'));
        foreach (glob($root.'/app/Http/Controllers/*.php') ?: [] as $file) {
            $count = substr_count((string) file_get_contents($file), 'LeadLifecycleAtomicEventOrchestrator');
            self::assertSame(str_ends_with($file, 'LeadLifecycleHttpController.php') ? 2 : 0, $count, $file);
        }
    }
}
