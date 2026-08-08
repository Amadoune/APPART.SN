<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConsentOwnerSourceRuntimeReadArchitectureTest extends TestCase
{
    public function test_runtime_read_is_owner_scoped_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/ConsentOwnerSourceRuntimeRead';
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getPathname();
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Store', 'Mapper', 'App\\Http'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }

        self::assertCount(7, $files);
    }

    public function test_runtime_read_provider_uses_only_the_owner_application_port(): void
    {
        $path = dirname(__DIR__, 2).'/app/Providers/ContactsLeadsConsentOwnerSourceRuntimeReadServiceProvider.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertStringContainsString('singleton', $contents);
        self::assertStringContainsString('ConsentOwnerSourceRuntimeReadV1::class', $contents);
        foreach (['PostgreSql', 'Infrastructure\\', 'ConsentOwnerSourceMapper', 'PDO', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_runtime_read_does_not_bind_or_implement_the_public_reader(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertFileDoesNotExist($root.'/src/Modules/ContactsLeads/Application/ConsentPublicRead/OwnerLeadContactConsentReaderV1.php');
        $provider = file_get_contents($root.'/app/Providers/ContactsLeadsConsentOwnerSourceRuntimeReadServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringNotContainsString('LeadContactConsentReaderV1', $provider);
    }
}
