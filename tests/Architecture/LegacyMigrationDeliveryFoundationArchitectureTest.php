<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LegacyMigrationDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_five_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/Delivery';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(25, $files);
        foreach (['Inventory', 'Wave', 'Reconciliation', 'Quarantine', 'Cutover'] as $stream) {
            self::assertStringContainsString('LegacyMigration'.$stream.'EventV1', $php);
        }
        self::assertSame(5, substr_count($php, 'public function create('));
        self::assertSame(5, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        self::assertStringNotContainsString('default', $php);
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_084_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $root.'084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $root.'084_legacy_migration_owner_source.down.sql'));
    }
}
