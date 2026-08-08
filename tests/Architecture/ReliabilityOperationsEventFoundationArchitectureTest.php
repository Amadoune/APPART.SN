<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReliabilityOperationsEventFoundationArchitectureTest extends TestCase
{
    public function test_exactly_seven_event_catalogues_depend_only_on_public_readers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/Event';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertCount(35, $files);
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'MaintenanceOperations', 'Continuity', 'CapacityPlanning', 'OperationalReadiness'] as $stream) {
            self::assertStringContainsString($stream.'ReaderV1', $php);
            self::assertSame(1, substr_count($php, 'final readonly class '.$stream.'EventFactory'));
        }
        self::assertSame(7, substr_count($php, "return ['status' => \$this->status->value, 'observedAt' => \$this->observedAt];"));
        foreach (['OwnerSource', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Delivery', 'Outbox', 'Consumer', 'default'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_types_are_exact_and_migration_088_is_unchanged(): void
    {
        $root = dirname(__DIR__, 2);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/src/Modules/ReliabilityOperations/Application/Event/*EventType.php') ?: []));
        foreach (['observability', 'service-health', 'alerting', 'maintenance-operations', 'continuity', 'capacity-planning', 'operational-readiness'] as $stream) {
            self::assertSame(1, substr_count($php, 'reliability-operations.'.$stream.'.observed.v1'));
        }
        $migrations = $root.'/src/Modules/ReliabilityOperations/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('1f0db96df9513b40b498f7daf9cd3c4607142f12b1b00bafffed7040e22aec3e', hash_file('sha256', $migrations.'088_reliability_operations_owner_source.sql'));
        self::assertSame('f25a5bf041f7a823c5856c17940a53ad956da0931ce1fd918a492e3236139bb2', hash_file('sha256', $migrations.'088_reliability_operations_owner_source.down.sql'));
    }
}
