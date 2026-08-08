<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrationConsoleHttpFoundationArchitectureTest extends TestCase
{
    public function test_controllers_depend_only_on_public_v1_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/Administration*Controller.php'), glob($root.'/app/Http/Requests/Administration*Request.php'), glob($root.'/app/Http/AdministrationConsole/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach (['AdministrationOperatorReaderV1', 'AdministrationQueueReaderV1', 'AdministrationAuditReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['AdministrationConsoleOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_registers_only_http_components(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/AdministrationConsoleHttpServiceProvider.php');
        foreach (['AdministrationConsoleResponseFactory', 'AdministrationOperatorController', 'AdministrationQueueController', 'AdministrationAuditController'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['AdministrationConsoleOwnerSource', 'PostgreSql', 'Mapper', 'RuntimeServiceProvider', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_provider_and_routes_are_unique(): void
    {
        $providers = (string) file_get_contents(dirname(__DIR__, 2).'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'AdministrationConsoleHttpServiceProvider'));

        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/AdministrationConsoleHttpServiceProvider.php');
        foreach (['administration.operator', 'administration.queue', 'administration.audit'] as $route) {
            self::assertSame(1, substr_count($provider, "name('".$route."')"));
        }
    }

    public function test_migration_082_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544', hash_file('sha256', $root.'082_administration_console_owner_source.sql'));
        self::assertSame('83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea', hash_file('sha256', $root.'082_administration_console_owner_source.down.sql'));
    }
}
