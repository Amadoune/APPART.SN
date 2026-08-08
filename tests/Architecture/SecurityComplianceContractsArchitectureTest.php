<?php

namespace Tests\Architecture;

use Appart\Modules\SecurityCompliance\Application\PublicRead\ComplianceControlStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\CryptographyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataExportStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\DataRetentionStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\IncidentStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\PrivacyPolicyStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityAuditStatusV1;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceContractsArchitectureTest extends TestCase
{
    public function test_contract_enclave_is_read_only_application_code(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/PublicRead';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(26, $files);
        foreach (['SecretInventory', 'CryptographyPolicy', 'SecurityAudit', 'Incident', 'PrivacyPolicy', 'DataRetention', 'DataExport', 'ComplianceControl'] as $name) {
            self::assertStringContainsString('interface '.$name.'ReaderV1', $php);
        }
        self::assertSame(8, substr_count($php, 'public function read('));
        self::assertStringNotContainsString('public function write(', $php);
        self::assertStringNotContainsString('public function append(', $php);
        foreach (['Infrastructure\\', 'Persistence', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider', 'PDO', 'PostgreSql', 'SQL', 'LegacyMigration', 'IdentityAccess'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_eight_catalogues_are_distinct_and_exactly_closed(): void
    {
        foreach ([SecretInventoryStatusV1::cases(), CryptographyPolicyStatusV1::cases(), SecurityAuditStatusV1::cases(), IncidentStatusV1::cases(), PrivacyPolicyStatusV1::cases(), DataRetentionStatusV1::cases(), DataExportStatusV1::cases(), ComplianceControlStatusV1::cases()] as $catalogue) {
            self::assertSame(['available', 'missing', 'corrupted', 'dependency_unavailable'], array_map(static fn ($status): string => $status->value, $catalogue));
        }
    }
}
