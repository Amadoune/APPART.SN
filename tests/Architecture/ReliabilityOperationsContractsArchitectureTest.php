<?php

namespace Tests\Architecture;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ReliabilityOperationsContractsArchitectureTest extends TestCase
{
    public function test_contract_enclave_is_minimal_read_only_application_code(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ReliabilityOperations/Application/PublicRead';
        $files = [];
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertCount(22, $files);
        foreach (['Observability', 'ServiceHealth', 'Alerting', 'Continuity', 'MaintenanceOperations', 'CapacityPlanning', 'OperationalReadiness'] as $name) {
            self::assertStringContainsString('interface '.$name.'ReaderV1', $php);
        }
        self::assertSame(7, substr_count($php, 'public function read('));
        foreach (['write(', 'append(', 'Infrastructure\\', 'Persistence', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider', 'PDO', 'PostgreSql', 'SQL', 'SecurityCompliance', 'LegacyMigration'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_seven_status_catalogues_are_exactly_closed(): void
    {
        self::assertSame(['available', 'degraded', 'missing', 'corrupted', 'dependency_unavailable'], self::values(ObservabilityStatusV1::cases()));
        self::assertSame(['healthy', 'degraded', 'unavailable', 'missing', 'corrupted', 'dependency_unavailable'], self::values(ServiceHealthStatusV1::cases()));
        self::assertSame(['ready', 'degraded', 'unavailable', 'missing', 'corrupted', 'dependency_unavailable'], self::values(AlertingStatusV1::cases()));
        self::assertSame(['ready', 'at_risk', 'blocked', 'missing', 'corrupted', 'dependency_unavailable'], self::values(ContinuityStatusV1::cases()));
        self::assertSame(['ready', 'degraded', 'blocked', 'missing', 'corrupted', 'dependency_unavailable'], self::values(MaintenanceOperationsStatusV1::cases()));
        self::assertSame(['sufficient', 'at_risk', 'exhausted', 'missing', 'corrupted', 'dependency_unavailable'], self::values(CapacityPlanningStatusV1::cases()));
        self::assertSame(['ready', 'at_risk', 'blocked', 'missing', 'corrupted', 'dependency_unavailable'], self::values(OperationalReadinessStatusV1::cases()));
    }

    /** @param array<int, object{value: string}> $cases
     * @return list<string>
     */
    private static function values(array $cases): array
    {
        return array_map(static fn (object $status): string => $status->value, $cases);
    }
}
