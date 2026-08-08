<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class SecurityComplianceHttpFoundationArchitectureTest extends TestCase
{
    public function test_http_surface_depends_only_on_five_public_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/{SecretInventory,SecurityAudit,Incident,PrivacyPolicy,ComplianceControl}Controller.php', GLOB_BRACE), glob($root.'/app/Http/Requests/{SecretInventory,SecurityAudit,Incident,PrivacyPolicy,ComplianceControl}Request.php', GLOB_BRACE), glob($root.'/app/Http/SecurityCompliance/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['SecretInventoryReaderV1', 'SecurityAuditReaderV1', 'IncidentReaderV1', 'PrivacyPolicyReaderV1', 'ComplianceControlReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['CryptographyPolicyReaderV1', 'DataRetentionReaderV1', 'DataExportReaderV1', 'SecurityComplianceOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_and_routes_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/SecurityComplianceHttpServiceProvider.php');
        foreach (['SecurityComplianceResponseFactory', 'SecretInventoryController', 'SecurityAuditController', 'IncidentController', 'PrivacyPolicyController', 'ComplianceControlController'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['secret-inventory', 'security-audit', 'incident', 'privacy-policy', 'compliance-control'] as $route) {
            self::assertSame(1, substr_count($provider, "name('security-compliance.".$route."')"));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'SecurityComplianceHttpServiceProvider'));
    }

    public function test_migration_086_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $root.'086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $root.'086_security_compliance_owner_source.down.sql'));
    }
}
