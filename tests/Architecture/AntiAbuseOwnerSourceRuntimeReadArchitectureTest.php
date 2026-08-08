<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AntiAbuseOwnerSourceRuntimeReadArchitectureTest extends TestCase
{
    public function test_runtime_read_is_owner_scoped_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/AntiAbuseOwnerSourceRuntimeRead';
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getPathname();
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Store', 'Mapper', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
        self::assertCount(8, $files);
    }

    public function test_provider_uses_only_owner_application_contracts(): void
    {
        $path = dirname(__DIR__, 2).'/app/Providers/ContactsLeadsAntiAbuseOwnerSourceRuntimeReadServiceProvider.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertStringContainsString('singleton', $contents);
        self::assertStringContainsString('LeadIngressAntiAbuseRuntimeReadV1::class', $contents);
        foreach (['PostgreSql', 'Infrastructure\\', 'AntiAbuseOwnerSourceMapper', 'PDO', 'RuntimeHealth', 'LeadIngressAntiAbuseReaderV1'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
