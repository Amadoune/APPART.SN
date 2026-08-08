<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_five_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/Delivery';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(25, $files);
        foreach (['SecretInventory', 'SecurityAudit', 'Incident', 'PrivacyPolicy', 'ComplianceControl'] as $family) {
            self::assertStringContainsString($family.'EventV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$family.'DeliveryFactory'));
        }
        self::assertSame(5, substr_count($php, 'public function create('));
        self::assertSame(5, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['CryptographyPolicyDelivery', 'DataRetentionDelivery', 'DataExportDelivery', 'OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_086_and_rollback_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $root.'086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $root.'086_security_compliance_owner_source.down.sql'));
    }
}
