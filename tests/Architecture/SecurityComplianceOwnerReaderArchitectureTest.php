<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceOwnerReaderArchitectureTest extends TestCase
{
    public function test_owner_readers_depend_only_on_owner_source_and_public_contracts(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/OwnerReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertSame(5, substr_count($php, 'implements ') - 1);
        self::assertStringContainsString('SecurityComplianceOwnerSource', $php);
        self::assertStringNotContainsString('default', $php);
        foreach (['CryptographyPolicyOwnerReader', 'DataRetentionOwnerReader', 'DataExportOwnerReader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'SQL', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Routing', 'Transport'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_exposes_exactly_five_public_aliases_and_one_policy_alias(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/SecurityComplianceOwnerReaderServiceProvider.php');
        foreach (['SecretInventoryReaderV1', 'SecurityAuditReaderV1', 'IncidentReaderV1', 'PrivacyPolicyReaderV1', 'ComplianceControlReaderV1'] as $contract) {
            self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*'.$contract.'::class\);/', $provider));
        }
        self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*SecurityComplianceOwnerReaderV1::class\);/', $provider));
        self::assertSame(6, substr_count($provider, '->singleton('));
        foreach (['CryptographyPolicyReaderV1', 'DataRetentionReaderV1', 'DataExportReaderV1', 'PostgreSql', 'Mapper', 'PDO', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'SecurityComplianceOwnerReaderServiceProvider'));
    }

    public function test_migration_086_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $root.'086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $root.'086_security_compliance_owner_source.down.sql'));
    }
}
