<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsOwnerSourcePersistenceArchitectureTest extends TestCase
{
    public function test_application_owner_source_is_owner_scoped_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/OwnerSource';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(7, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['observability', 'service_health', 'alerting', 'continuity', 'maintenance_operations', 'capacity_planning', 'operational_readiness'] as $stream) {
            self::assertStringContainsString($stream, $php);
        }
        foreach (['Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_and_migration_materialize_only_persistence_guarantees(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/PostgreSqlReliabilityOperationsOwnerSource.php');
        foreach (['SAVEPOINT', 'pg_advisory_xact_lock', 'FOR UPDATE', 'ON CONFLICT', 'effective_at<=', 'recorded_at<='] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        foreach (['Application\\Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $repository);
        }
        $migrations = glob($root.'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/*.sql') ?: [];
        self::assertCount(2, $migrations);
        self::assertStringContainsString('088_reliability_operations_owner_source.sql', implode('|', $migrations));
        self::assertStringContainsString('088_reliability_operations_owner_source.down.sql', implode('|', $migrations));
    }
}
