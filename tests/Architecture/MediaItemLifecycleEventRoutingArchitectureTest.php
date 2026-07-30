<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class MediaItemLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_routing_is_isolated_from_business_and_delivery_execution(): void
    {
        $root = dirname(__DIR__, 2);
        $contents = '';
        foreach ([$root.'/app/Application/MediaItemLifecycleEventRouting', $root.'/app/Infrastructure/MediaItemLifecycleEventRouting'] as $path) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $contents .= file_get_contents($file->getPathname());
                }
            }
        }
        foreach (['Outbox', 'Consumer', 'Worker', 'Http', 'Projection', 'Workflow', 'LeadEligibilityProof'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_is_owner_scoped_append_only(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/033_media_item_lifecycle_event_inbox.sql');
        self::assertStringContainsString('media.media_item_lifecycle_event_inbox', $migration);
        self::assertStringContainsString('UNIQUE', $migration);
        self::assertStringContainsString('canonical_event text NOT NULL', $migration);
        self::assertStringNotContainsString('UPDATE ', $migration);
        self::assertStringNotContainsString('email', $migration);
        self::assertStringNotContainsString('phone', $migration);
    }
}
