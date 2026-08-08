<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReliabilityOperationsOutboxFoundationArchitectureTest extends TestCase
{
    public function test_outbox_is_owner_scoped_and_has_no_forbidden_dependencies(): void
    {
        $root = dirname(__DIR__, 2);
        $paths = [$root.'/src/Modules/ReliabilityOperations/Application/Outbox', $root.'/src/Modules/ReliabilityOperations/Infrastructure/Outbox'];
        $php = '';
        foreach ($paths as $path) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $php .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        self::assertStringContainsString("OWNER = 'ReliabilityOperations'", $php);
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'MaintenanceOperations', 'Continuity', 'CapacityPlanning', 'OperationalReadiness'] as $family) {
            self::assertStringContainsString($family.'DeliveryV1', $php);
        }
        foreach (['Transport', 'Routing', 'Consumer', 'App\\Http', 'Controller', 'Request', 'RuntimeRead', 'OwnerReader', 'Application\\Runtime', 'PublicRead\\Contract'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_concurrency_transactions_retry_and_schema_are_explicit(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ReliabilityOperations/Infrastructure/Outbox/PostgreSqlReliabilityOperationsOutboxRepository.php');
        $policy = (string) file_get_contents($root.'/src/Modules/ReliabilityOperations/Application/Outbox/ReliabilityOperationsOutboxPolicy.php');
        foreach (['ROLLBACK TO SAVEPOINT', 'FOR UPDATE OF s SKIP LOCKED', 'ON CONFLICT DO NOTHING'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        foreach (['MAX_ATTEMPTS = 10', "SAVEPOINT = 'reliability_operations_outbox'"] as $guarantee) {
            self::assertStringContainsString($guarantee, $policy);
        }
        $migration = (string) file_get_contents($root.'/src/Modules/ReliabilityOperations/Infrastructure/Outbox/Migrations/089_reliability_operations_outbox.sql');
        foreach (['outbox_message_journal', 'outbox_message_state', "owner_name = 'ReliabilityOperations'", 'UNIQUE', 'attempts BETWEEN 0 AND 10'] as $guarantee) {
            self::assertStringContainsString($guarantee, $migration);
        }
        self::assertStringNotContainsString('ON DELETE CASCADE', $migration);
    }

    public function test_migrations_084_to_088_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $expected = [
            '/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.sql' => 'a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591',
            '/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/084_legacy_migration_owner_source.down.sql' => 'e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d',
            '/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/085_legacy_migration_outbox.sql' => 'c13d421db86e0e3ae67f9b20e677a61c9a4bc430441bfa811977e5dda744c994',
            '/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/085_legacy_migration_outbox.down.sql' => '6576df3dea08c862cc796aa73497b55bc48f4fcb67fe814462d773a65f3e83df',
            '/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.sql' => 'b0e5e97d49f4b9f9f901db9e4ff61d6c12e50ba67e66c300bb9a0e610803ee93',
            '/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/086_security_compliance_owner_source.down.sql' => '113b3965a12d9c7927cc596fb6dfc1607e85f11e71ac50f0c546e30eeac97e57',
            '/src/Modules/SecurityCompliance/Infrastructure/Outbox/Migrations/087_security_compliance_outbox.sql' => '56b9ce96e101729bc48471b9adeef17566215230f150775a5147c37be5f9c107',
            '/src/Modules/SecurityCompliance/Infrastructure/Outbox/Migrations/087_security_compliance_outbox.down.sql' => 'e42c805ab0b407113884385754d852cefcfeb84fe8c0d194d8412b0da8513389',
            '/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/088_reliability_operations_owner_source.sql' => '1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e',
            '/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/088_reliability_operations_owner_source.down.sql' => 'f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2',
        ];
        foreach ($expected as $path => $hash) {
            self::assertSame($hash, hash_file('sha256', $root.$path));
        }
    }
}
