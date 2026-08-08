<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class SecurityComplianceOwnerSourcePersistenceArchitectureTest extends TestCase
{
    public function test_application_owner_source_has_exactly_five_independent_streams(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SecurityCompliance/Application/OwnerSource';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(16, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['SecretInventory', 'SecurityAudit', 'Incident', 'PrivacyPolicy', 'ComplianceControl'] as $stream) {
            self::assertStringContainsString('append'.$stream, $php);
            self::assertStringContainsString('read'.$stream, $php);
        }
        foreach (['Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_and_migration_materialize_only_the_required_guarantees(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/PostgreSqlSecurityComplianceOwnerSource.php');
        foreach (['SAVEPOINT', 'pg_advisory_xact_lock', 'FOR UPDATE', 'ON CONFLICT', 'effective_at<=', 'recorded_at<='] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        $migrations = glob($root.'/src/Modules/SecurityCompliance/Infrastructure/Persistence/PostgreSql/Migrations/*.sql') ?: [];
        self::assertCount(2, $migrations);
        self::assertStringContainsString('086_security_compliance_owner_source.sql', implode('|', $migrations));
        self::assertStringContainsString('086_security_compliance_owner_source.down.sql', implode('|', $migrations));
    }
}
