<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_routing_is_isolated_from_business_sources_and_delivery_execution(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [
            $root.'/app/Application/LeadLifecycleEventRouting',
            $root.'/app/Infrastructure/LeadLifecycleEventRouting',
        ];
        $contents = '';
        foreach ($paths as $path) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $contents .= file_get_contents($file->getPathname());
                }
            }
        }

        foreach (['ListingCatalog', 'AdvertiserCatalog', 'LeadEligibilityProof', 'Outbox', 'Consumer', 'Worker', 'Http', 'Projection', 'Workflow'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_is_owner_scoped_append_only_and_has_no_business_fields(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/LeadLifecycleEventRouting/PostgreSql/Migrations/025_lead_lifecycle_event_inbox.sql');

        self::assertStringContainsString('contacts_leads.lead_lifecycle_event_inbox', $migration);
        self::assertStringContainsString('UNIQUE', $migration);
        self::assertStringContainsString('canonical_event text NOT NULL', $migration);
        self::assertStringNotContainsString('UPDATE ', $migration);
        self::assertStringNotContainsString('email', $migration);
        self::assertStringNotContainsString('phone', $migration);
    }
}
