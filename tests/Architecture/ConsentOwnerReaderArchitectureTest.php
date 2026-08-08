<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ConsentOwnerReaderArchitectureTest extends TestCase
{
    public function test_reader_depends_only_on_public_contract_and_runtime_read(): void
    {
        $path = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/ConsentOwnerReader/OwnerLeadContactConsentReaderV1.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertStringContainsString('ConsentOwnerSourceRuntimeReadV1', $contents);
        self::assertStringContainsString('LeadContactConsentReaderV1', $contents);
        foreach (['ConsentOwnerSource\\', 'Infrastructure\\', 'Persistence\\', 'PostgreSql', 'PDO', 'Mapper', 'RuntimeAvailability', 'RuntimeDiagnostics'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_provider_is_dedicated_and_does_not_compose_infrastructure(): void
    {
        $path = dirname(__DIR__, 2).'/app/Providers/ContactsLeadsConsentOwnerReaderServiceProvider.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertStringContainsString('singleton', $contents);
        self::assertStringContainsString('LeadContactConsentReaderV1::class', $contents);
        foreach (['Infrastructure\\', 'PostgreSql', 'PDO', 'Mapper', 'ConsentOwnerSource::class'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
