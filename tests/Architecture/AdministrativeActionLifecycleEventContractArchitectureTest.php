<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_contract_is_pure_and_contains_no_transport_or_persistence(): void
    {
        $directory = dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecycleEvent';
        $files = new \FilesystemIterator($directory);
        foreach ($files as $file) {
            $source = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'PostgreSql', 'Repository', 'Inbox', 'Outbox', 'Router', 'Consumer', 'Worker', 'Http', 'Illuminate\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $file->getFilename());
            }
        }
    }

    public function test_catalog_is_closed_to_exactly_four_certified_facts(): void
    {
        $types = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/AdministrationAudit/Application/AdministrativeActionLifecycleEvent/AdministrativeActionLifecycleEventType.php');
        self::assertSame(4, substr_count($types, 'case '));
        foreach (['recorded', 'approval_requested', 'approved', 'rejected'] as $fact) {
            self::assertStringContainsString($fact, $types);
        }
    }

    public function test_contract_serializer_remains_unbound_and_contract_files_remain_runtime_free(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertStringNotContainsString('AdministrativeActionLifecycleEventSerializer::class', $provider);
    }
}
