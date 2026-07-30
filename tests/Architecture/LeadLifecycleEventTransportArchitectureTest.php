<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_transport_foundation_has_no_execution_infrastructure_or_business_source_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/LeadLifecycleEventTransport';
        $contents = '';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile()) {
                $contents .= file_get_contents($file->getPathname());
            }
        }

        foreach (['PostgreSql', 'PDO', 'Laravel', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Http', 'ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
