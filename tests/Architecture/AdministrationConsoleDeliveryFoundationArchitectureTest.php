<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrationConsoleDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_administration_console_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/Delivery';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertCount(15, $files);
        foreach (['AdministrationOperatorEventV1', 'AdministrationQueueEventV1', 'AdministrationAuditEventV1'] as $event) {
            self::assertStringContainsString($event, $php);
        }
        self::assertSame(3, substr_count($php, 'public function create('));
        self::assertSame(3, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        self::assertStringNotContainsString('default', $php);
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox'] as $forbidden) {
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
