<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_routing_is_isolated_from_business_and_delivery_execution(): void
    {
        $root = dirname(__DIR__, 2);
        $contents = '';

        foreach ([
            $root.'/app/Application/PlaceLifecycleEventRouting',
            $root.'/app/Infrastructure/PlaceLifecycleEventRouting',
        ] as $path) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $contents .= file_get_contents($file->getPathname());
                }
            }
        }

        foreach (['Outbox', 'Consumer', 'Worker', 'Http', 'Projection', 'Workflow', 'PlaceRegistry'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_is_owner_scoped_append_only_and_runtime_is_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents(
            $root.'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/039_place_lifecycle_event_inbox.sql',
        );
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertStringContainsString('geography.place_lifecycle_event_inbox', $migration);
        self::assertStringContainsString('message_id varchar(128) NOT NULL UNIQUE', $migration);
        self::assertStringContainsString('canonical_event text NOT NULL', $migration);
        self::assertStringNotContainsString('UPDATE ', $migration);
        self::assertSame(3, substr_count($provider, 'DurablePlaceLifecycleEventRouter'));
        self::assertSame(4, substr_count($provider, 'PlaceLifecycleInboxStore'));
    }
}
