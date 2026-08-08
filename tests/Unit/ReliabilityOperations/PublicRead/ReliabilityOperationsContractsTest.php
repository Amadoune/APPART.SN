<?php

namespace Tests\Unit\ReliabilityOperations\PublicRead;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReliabilityOperationsContractsTest extends TestCase
{
    #[DataProvider('results')]
    public function test_results_expose_only_status_and_canonical_observed_at(object $result, string $status): void
    {
        self::assertSame($status, $result->status->value);
        self::assertSame('2026-08-07T10:00:00.123456Z', $result->observedAt);
        $properties = array_keys(get_object_vars($result));
        sort($properties);
        self::assertSame(['observedAt', 'status'], $properties);
    }

    /** @return iterable<string, array{object, string}> */
    public static function results(): iterable
    {
        $at = new ReliabilityOperationsObservedAt(new DateTimeImmutable('2026-08-07T12:00:00.123456+02:00'));
        foreach (ObservabilityStatusV1::cases() as $status) {
            yield 'observability '.$status->value => [new ObservabilityResultV1($status, $at), $status->value];
        }
        foreach (ServiceHealthStatusV1::cases() as $status) {
            yield 'health '.$status->value => [new ServiceHealthResultV1($status, $at), $status->value];
        }
        foreach (AlertingStatusV1::cases() as $status) {
            yield 'alerting '.$status->value => [new AlertingResultV1($status, $at), $status->value];
        }
        foreach (ContinuityStatusV1::cases() as $status) {
            yield 'continuity '.$status->value => [new ContinuityResultV1($status, $at), $status->value];
        }
        foreach (MaintenanceOperationsStatusV1::cases() as $status) {
            yield 'maintenance '.$status->value => [new MaintenanceOperationsResultV1($status, $at), $status->value];
        }
        foreach (CapacityPlanningStatusV1::cases() as $status) {
            yield 'capacity '.$status->value => [new CapacityPlanningResultV1($status, $at), $status->value];
        }
        foreach (OperationalReadinessStatusV1::cases() as $status) {
            yield 'readiness '.$status->value => [new OperationalReadinessResultV1($status, $at), $status->value];
        }
    }
}
