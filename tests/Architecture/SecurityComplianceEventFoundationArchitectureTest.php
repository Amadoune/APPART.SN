<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceEventFoundationArchitectureTest extends TestCase
{
    public function test_exactly_five_event_catalogues_depend_only_on_public_v1_readers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/Event';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(25, $files);
        foreach (['SecretInventory', 'SecurityAudit', 'Incident', 'PrivacyPolicy', 'ComplianceControl'] as $stream) {
            self::assertStringContainsString($stream.'ReaderV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$stream.'EventFactory'));
        }
        self::assertSame(5, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['CryptographyPolicyEvent', 'DataRetentionEvent', 'DataExportEvent', 'OwnerSource', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Delivery', 'Outbox', 'Consumer', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_types_are_exact_and_migration_086_is_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/src/Modules/SecurityCompliance/Application/Event/*EventType.php') ?: []));
        foreach (['secret-inventory', 'security-audit', 'incident', 'privacy-policy', 'compliance-control'] as $stream) {
            self::assertSame(1, substr_count($php, 'security-compliance.'.$stream.'.observed.v1'));
        }
        $migrations = $root.'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $migrations.'086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $migrations.'086_security_compliance_owner_source.down.sql'));
    }
}
