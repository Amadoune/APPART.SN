<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SecurityComplianceOutboxFoundationArchitectureTest extends TestCase
{
    public function test_outbox_is_owner_scoped_and_has_no_forbidden_dependencies(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [$root.'/src/Modules/SecurityCompliance/Application/Outbox', $root.'/src/Modules/SecurityCompliance/Infrastructure/Outbox'];
        $php = '';
        foreach ($paths as $path) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $php .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        self::assertStringContainsString("OWNER = 'SecurityCompliance'", $php);
        foreach (['SecretInventoryDeliveryV1', 'SecurityAuditDeliveryV1', 'IncidentDeliveryV1', 'PrivacyPolicyDeliveryV1', 'ComplianceControlDeliveryV1'] as $delivery) {
            self::assertStringContainsString($delivery, $php);
        }
        foreach (['CryptographyPolicy', 'DataRetention', 'DataExport', 'LegacyMigration', 'AdministrationConsole', 'Transport', 'Routing', 'Consumer', 'App\\Http', 'Controller', 'Request', 'RuntimeRead', 'OwnerReader', 'Application\\Runtime', 'PublicRead\\Contract'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_concurrency_transactions_retry_and_schema_are_explicit(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/SecurityCompliance/Infrastructure/Outbox/PostgreSqlSecurityComplianceOutboxRepository.php');
        foreach (['SAVEPOINT', 'ROLLBACK TO SAVEPOINT', 'FOR UPDATE OF s SKIP LOCKED', 'ON CONFLICT DO NOTHING', 'MAX_ATTEMPTS = 10'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        $migration = (string) file_get_contents($root.'/src/Modules/SecurityCompliance/Infrastructure/Outbox/Migrations/087_security_compliance_outbox.sql');
        foreach (['outbox_message_journal', 'outbox_message_state', "owner_name = 'SecurityCompliance'", 'UNIQUE', 'attempts BETWEEN 0 AND 10'] as $guarantee) {
            self::assertStringContainsString($guarantee, $migration);
        }
        self::assertStringNotContainsString('ON DELETE CASCADE', $migration);
    }

    public function test_migrations_084_to_086_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.down.sql'));
        self::assertSame('c13d421db86e0e3ae67f9b20e677a61c9a4bc430441bfa811977e5dda744c994', hash_file('sha256', $root.'/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/085_legacy_migration_outbox.sql'));
        self::assertSame('6576df3dea08c862cc796aa73497b55bc48f4fcb67fe814462d773a65f3e83df', hash_file('sha256', $root.'/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/085_legacy_migration_outbox.down.sql'));
        self::assertSame('b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93', hash_file('sha256', $root.'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.sql'));
        self::assertSame('113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57', hash_file('sha256', $root.'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.down.sql'));
    }
}
