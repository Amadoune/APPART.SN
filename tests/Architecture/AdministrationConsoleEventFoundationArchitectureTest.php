<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrationConsoleEventFoundationArchitectureTest extends TestCase
{
    public function test_event_catalogues_depend_only_on_public_v1_readers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/Event';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertCount(15, $files);
        foreach (['AdministrationOperatorReaderV1', 'AdministrationQueueReaderV1', 'AdministrationAuditReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['AdministrationOperatorEventFactory', 'AdministrationQueueEventFactory', 'AdministrationAuditEventFactory'] as $factory) {
            self::assertSame(1, substr_count($php, 'final readonly class '.$factory));
        }
        self::assertSame(3, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        self::assertStringNotContainsString('default', $php);
        foreach (['OwnerSource', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Delivery', 'Outbox', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_082_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544', hash_file('sha256', $root.'082_administration_console_owner_source.sql'));
        self::assertSame('83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea', hash_file('sha256', $root.'082_administration_console_owner_source.down.sql'));
    }
}
