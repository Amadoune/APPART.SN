<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AdministrationConsoleOwnerReaderArchitectureTest extends TestCase
{
    public function test_owner_readers_depend_on_the_owner_source_and_public_contracts_only(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Application/OwnerReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertSame(3, substr_count($php, 'implements Administration') - 1);
        self::assertStringContainsString('AdministrationConsoleOwnerSource', $php);
        self::assertStringNotContainsString('default', $php);
        foreach (['Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'SQL', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Routing', 'Transport'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_exposes_exactly_three_public_reader_aliases_and_one_policy_alias(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/AdministrationConsoleOwnerReaderServiceProvider.php');
        foreach (['AdministrationOperatorReaderV1', 'AdministrationQueueReaderV1', 'AdministrationAuditReaderV1'] as $contract) {
            self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*'.$contract.'::class\);/', $provider));
        }
        self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*AdministrationConsoleOwnerReaderV1::class\);/', $provider));
        self::assertSame(4, substr_count($provider, '->singleton('));
        foreach (['PostgreSql', 'Mapper', 'PDO', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_provider_is_registered_once(): void
    {
        $providers = (string) file_get_contents(dirname(__DIR__, 2).'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'AdministrationConsoleOwnerReaderServiceProvider'));
    }

    public function test_migration_082_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/AdministrationConsole/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('ed6987ae4605d723d25b7e58b5148535e650d1bec397302dbb202da6abc3e544', hash_file('sha256', $root.'082_administration_console_owner_source.sql'));
        self::assertSame('83f175e530ca739469e86d7c47a9773e0082d7c20f1424a36dfafb4493208fea', hash_file('sha256', $root.'082_administration_console_owner_source.down.sql'));
    }
}
