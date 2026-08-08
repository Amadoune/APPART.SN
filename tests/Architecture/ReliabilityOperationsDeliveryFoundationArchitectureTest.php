<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReliabilityOperationsDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_seven_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/Delivery';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(35, $files);
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'MaintenanceOperations', 'Continuity', 'CapacityPlanning', 'OperationalReadiness'] as $family) {
            self::assertStringContainsString($family.'EventV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$family.'DeliveryFactory'));
        }
        self::assertSame(7, substr_count($php, 'public function create('));
        self::assertSame(7, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_088_and_rollback_remain_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e', hash_file('sha256', $root.'088_reliability_operations_owner_source.sql'));
        self::assertSame('f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2', hash_file('sha256', $root.'088_reliability_operations_owner_source.down.sql'));
    }
}
