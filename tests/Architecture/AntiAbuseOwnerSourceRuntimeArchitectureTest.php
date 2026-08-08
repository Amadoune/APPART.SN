<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AntiAbuseOwnerSourceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/AntiAbuseOwnerSourceRuntime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_is_owner_scoped_and_bindings_are_unique(): void
    {
        $provider = dirname(__DIR__, 2).'/app/Providers/ContactsLeadsAntiAbuseOwnerSourceRuntimeServiceProvider.php';
        $contents = file_get_contents($provider);
        self::assertIsString($contents);
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlAntiAbuseOwnerSource::class, AntiAbuseOwnerSource::class\)/', $contents));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicAntiAbuseOwnerSourceRuntimeV1::class, AntiAbuseOwnerSourceRuntimeV1::class\)/', $contents));
        self::assertStringContainsString('PostgreSqlAntiAbuseOwnerSource::class', $contents);
        self::assertStringContainsString('singleton', $contents);
        foreach (['IdentityAccess', 'ListingLifecycle', 'Professional', 'Moderation', 'AdministrationAudit', 'RuntimeHealthInspector'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_runtime_does_not_open_forbidden_surfaces_or_change_migration(): void
    {
        $application = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/AntiAbuseOwnerSourceRuntime';
        self::assertDirectoryDoesNotExist($application.'/Reader');
        $migration = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Infrastructure/Persistence/PostgreSql/Migrations/073_anti_abuse_owner_local_source.sql');
        self::assertIsString($migration);
        self::assertSame('cfae6ec9a3064ecd81257d4ea2ae9cb668ea7039204aedbac39874e47952d450', hash('sha256', $migration));
    }
}
