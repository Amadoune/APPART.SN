<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrationConsoleRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/Runtime';
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
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/AdministrationConsoleRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlAdministrationConsoleOwnerSource::class, AdministrationConsoleOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicAdministrationConsoleRuntimeAvailabilityPolicy::class, AdministrationConsoleRuntimeAvailabilityPolicy::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicAdministrationConsoleRuntime::class, AdministrationConsoleRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);
        foreach (['IdentityAccess', 'Moderation', 'Notifications', 'ContentSeo', 'AdministrationAudit', 'RuntimeHealth', 'Http', 'Event', 'Delivery', 'Outbox', 'Consumer', 'Transport', 'Routing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_migration_082_is_frozen_by_sha256(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544', hash_file('sha256', $root.'082_administration_console_owner_source.sql'));
        self::assertSame('83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea', hash_file('sha256', $root.'082_administration_console_owner_source.down.sql'));
    }
}
