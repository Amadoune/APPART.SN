<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ConsentContractsMaterializationArchitectureTest extends TestCase
{
    public function test_contracts_are_owner_scoped_and_framework_agnostic(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/ConsentPublicRead';
        $files = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getPathname();
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);
            self::assertStringContainsString('namespace Appart\Modules\ContactsLeads\Application\ConsentPublicRead', $contents);
            foreach (['Illuminate\\', 'Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime\\', 'Persistence\\', 'App\\Providers', 'App\\Http'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }

        self::assertCount(4, $files);
    }

    public function test_materialization_remains_contract_only_and_owner_reader_binding_is_strictly_bounded(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertFileDoesNotExist($root.'/src/Modules/ContactsLeads/Application/ConsentPublicRead/OwnerLeadContactConsentReaderV1.php');

        $bindingProviders = [];
        foreach (glob($root.'/app/Providers/*.php') ?: [] as $providerPath) {
            $contents = file_get_contents($providerPath);
            self::assertIsString($contents);
            if (str_contains($contents, 'LeadContactConsentReaderV1::class')) {
                $bindingProviders[] = basename($providerPath);
            }
        }
        self::assertSame(['ContactsLeadsConsentOwnerReaderServiceProvider.php'], $bindingProviders);

        $provider = file_get_contents($root.'/app/Providers/ContactsLeadsConsentOwnerReaderServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringContainsString('OwnerLeadContactConsentReaderV1::class', $provider);
        self::assertStringContainsString(
            'alias(OwnerLeadContactConsentReaderV1::class, LeadContactConsentReaderV1::class)',
            $provider,
        );

        $readerPath = $root.'/src/Modules/ContactsLeads/Application/ConsentOwnerReader/OwnerLeadContactConsentReaderV1.php';
        self::assertFileExists($readerPath);
        $reader = file_get_contents($readerPath);
        self::assertIsString($reader);
        self::assertStringContainsString('ConsentOwnerSourceRuntimeReadV1', $reader);
        foreach (['ConsentOwnerSource\\', 'Infrastructure\\', 'Persistence\\', 'PostgreSql', 'PDO', 'Mapper', 'RuntimeAvailability', 'RuntimeDiagnostics'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $reader);
        }
    }
}
