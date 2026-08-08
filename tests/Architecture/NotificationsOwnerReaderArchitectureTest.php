<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NotificationsOwnerReaderArchitectureTest extends TestCase
{
    public function test_readers_depend_on_owner_source_and_public_contracts_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Notifications/Application/OwnerReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertSame(3, substr_count($php, 'implements Notification') - 1);
        self::assertStringContainsString('NotificationsOwnerSource', $php);
        foreach (['Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'SQL', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Routing', 'Transport'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_exposes_exactly_three_public_reader_aliases(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/NotificationsOwnerReaderServiceProvider.php');
        foreach (['NotificationPreferenceReaderV1', 'NotificationTemplateReaderV1', 'NotificationChannelReaderV1'] as $contract) {
            self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*'.$contract.'::class\);/', $provider));
        }
        foreach (['PostgreSql', 'Mapper', 'PDO', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_migration_079_remains_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql');
        self::assertSame('86ebe9e9c191f81822526321ecc960334dcb5869d84882bcec145de5b4f711ad', hash('sha256', $migration));
    }
}
