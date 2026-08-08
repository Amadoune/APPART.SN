<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NotificationsDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_notification_event_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Notifications/Application/Delivery';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertStringContainsString('NotificationEventV1', $php);
        self::assertSame(1, substr_count($php, 'public function create('));
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_079_remains_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql');
        self::assertSame('86ebe9e9c191f81822526321ecc960334dcb5869d84882bcec145de5b4f711ad', hash('sha256', $migration));
    }
}
