<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConsentOwnerSourceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/ConsentOwnerSourceRuntime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_composes_only_certified_owner_components(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Providers/ContactsLeadsConsentOwnerSourceRuntimeServiceProvider.php');
        self::assertIsString($contents);
        self::assertStringContainsString('ConsentOwnerSource::class', $contents);
        self::assertStringContainsString('PostgreSqlConsentOwnerSource::class', $contents);
        self::assertStringContainsString('singleton', $contents);
        foreach (['IdentityAccess', 'ListingLifecycle', 'Professional', 'Moderation', 'AdministrationAudit', 'RuntimeHealthInspector'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_runtime_does_not_create_forbidden_surfaces(): void
    {
        $paths = [
            dirname(__DIR__, 2).'/routes/web.php',
            dirname(__DIR__, 2).'/routes/api.php',
        ];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $contents = file_get_contents($path);
                self::assertIsString($contents);
                self::assertStringNotContainsString('consent-owner-source-runtime', $contents);
            }
        }
    }
}
