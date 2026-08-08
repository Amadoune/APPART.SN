<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AntiAbuseOwnerReaderArchitectureTest extends TestCase
{
    public function test_reader_depends_exclusively_on_runtime_read(): void
    {
        $path = dirname(__DIR__, 2).'/src/Modules/ContactsLeads/Application/AntiAbuseOwnerReader/OwnerLeadIngressAntiAbuseReaderV1.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertStringContainsString('LeadIngressAntiAbuseRuntimeReadV1', $contents);
        foreach (['AntiAbuseOwnerSource\\', 'PostgreSql', 'Infrastructure\\', 'PDO', 'Store', 'Mapper', 'RuntimeHealth', 'Illuminate\\', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_provider_only_binds_public_contract_to_owner_reader(): void
    {
        $path = dirname(__DIR__, 2).'/app/Providers/ContactsLeadsAntiAbuseOwnerReaderServiceProvider.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents);
        self::assertSame(1, substr_count($contents, 'singleton(OwnerLeadIngressAntiAbuseReaderV1::class)'));
        self::assertSame(1, substr_count($contents, 'alias(OwnerLeadIngressAntiAbuseReaderV1::class, LeadIngressAntiAbuseReaderV1::class)'));
        foreach (['PostgreSql', 'Infrastructure\\', 'PDO', 'AntiAbuseOwnerSourceMapper', 'RuntimeHealth'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
