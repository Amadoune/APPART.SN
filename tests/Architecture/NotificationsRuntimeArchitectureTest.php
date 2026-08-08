<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NotificationsRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/Notifications/Application/Runtime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'RuntimeRead', 'Reader', 'RuntimeHealth', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_are_unique_singletons(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/NotificationsRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlNotificationsOwnerSource::class, NotificationsOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicNotificationsRuntime::class, NotificationsRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);
        foreach (['ListingLifecycle', 'SearchDiscovery', 'IdentityAccess', 'RuntimeHealth', 'Http', 'Event', 'Delivery', 'Outbox', 'Consumer', 'Transport', 'Routing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_migration_079_is_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Notifications/Infrastructure/Persistence/PostgreSql/Migrations/079_notifications_owner_source.sql');
        self::assertSame('86ebe9e9c191f81822526321ecc960334dcb5869d84882bcec145de5b4f711ad', hash('sha256', $migration));
    }
}
