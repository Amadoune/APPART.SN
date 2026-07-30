<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrativeActionLifecycleEventRoutingArchitectureTest extends TestCase
{
    public function test_routing_is_isolated_from_business_and_delivery_execution(): void
    {
        $root = dirname(__DIR__, 2);
        $contents = '';

        foreach ([
            $root.'/app/Application/AdministrativeActionLifecycleEventRouting',
            $root.'/app/Infrastructure/AdministrativeActionLifecycleEventRouting',
        ] as $path) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $contents .= file_get_contents($file->getPathname());
                }
            }
        }

        foreach (['Outbox', 'Consumer', 'Worker', 'Http', 'Projection', 'Workflow', 'DecisionContext', 'HistoricalReason'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_is_owner_scoped_append_only(): void
    {
        $migration = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/036_administrative_action_lifecycle_event_inbox.sql',
        );

        self::assertStringContainsString('administration_audit.administrative_action_lifecycle_event_inbox', $migration);
        self::assertStringContainsString('UNIQUE', $migration);
        self::assertStringContainsString('canonical_event text NOT NULL', $migration);
        self::assertStringNotContainsString('UPDATE ', $migration);
        self::assertStringNotContainsString('reason', $migration);
        self::assertStringNotContainsString('decision_context', $migration);
    }

    public function test_runtime_binding_is_added_only_by_the_certified_composition_sprint(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(3, substr_count($provider, 'DurableAdministrativeActionLifecycleEventRouter'));
        self::assertSame(4, substr_count($provider, 'AdministrativeActionLifecycleInboxStore'));
    }
}
