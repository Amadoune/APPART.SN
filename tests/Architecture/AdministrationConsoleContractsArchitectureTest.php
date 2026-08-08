<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrationConsoleContractsArchitectureTest extends TestCase
{
    public function test_contract_enclave_is_application_only_and_read_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/PublicRead';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        foreach (['AdministrationOperatorReaderV1', 'AdministrationQueueReaderV1', 'AdministrationAuditReaderV1'] as $reader) {
            self::assertStringContainsString('interface '.$reader, $php);
        }
        self::assertSame(3, substr_count($php, 'public function read('));
        self::assertStringNotContainsString('public function write(', $php);
        foreach (['IdentityAccess', 'ModerationReports', 'Notifications', 'ContentSeo', 'AdministrationAudit\\', 'Infrastructure\\', 'Persistence', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider', 'PDO', 'PostgreSql', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }
}
