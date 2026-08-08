<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsHttpFoundationArchitectureTest extends TestCase
{
    public function test_http_surface_depends_only_on_seven_public_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(
            glob($root.'/app/Http/Controllers/ReliabilityOperations*Controller.php') ?: [],
            glob($root.'/app/Http/Requests/ReliabilityOperations*Request.php') ?: [],
            glob($root.'/app/Http/ReliabilityOperations/*.php') ?: [],
        );
        self::assertCount(16, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['ObservabilityReaderV1', 'ServiceHealthReaderV1', 'AlertingReaderV1', 'MaintenanceOperationsReaderV1', 'ContinuityReaderV1', 'CapacityPlanningReaderV1', 'OperationalReadinessReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['ReliabilityOperationsOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_and_routes_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/ReliabilityOperationsHttpServiceProvider.php');
        self::assertSame(8, substr_count($provider, '->singleton('));
        foreach (['observability', 'service-health', 'alerting', 'maintenance-operations', 'continuity', 'capacity-planning', 'operational-readiness'] as $route) {
            self::assertSame(1, substr_count($provider, "name('reliability-operations.".$route."')"));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'ReliabilityOperationsHttpServiceProvider'));
    }

    public function test_migration_088_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e', hash_file('sha256', $root.'088_reliability_operations_owner_source.sql'));
        self::assertSame('f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2', hash_file('sha256', $root.'088_reliability_operations_owner_source.down.sql'));
    }
}
