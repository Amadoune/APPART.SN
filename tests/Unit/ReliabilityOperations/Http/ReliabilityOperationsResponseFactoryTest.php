<?php

namespace Tests\Unit\ReliabilityOperations\Http;

use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
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
use BackedEnum;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class ReliabilityOperationsResponseFactoryTest extends TestCase
{
    public function test_observability_mapping_is_exhaustive(): void
    {
        foreach (ObservabilityStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->observability(new ObservabilityResultV1($status, self::at())));
        }
    }

    public function test_service_health_mapping_is_exhaustive(): void
    {
        foreach (ServiceHealthStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->serviceHealth(new ServiceHealthResultV1($status, self::at())));
        }
    }

    public function test_alerting_mapping_is_exhaustive(): void
    {
        foreach (AlertingStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->alerting(new AlertingResultV1($status, self::at())));
        }
    }

    public function test_maintenance_mapping_is_exhaustive(): void
    {
        foreach (MaintenanceOperationsStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->maintenanceOperations(new MaintenanceOperationsResultV1($status, self::at())));
        }
    }

    public function test_continuity_mapping_is_exhaustive(): void
    {
        foreach (ContinuityStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->continuity(new ContinuityResultV1($status, self::at())));
        }
    }

    public function test_capacity_mapping_is_exhaustive(): void
    {
        foreach (CapacityPlanningStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->capacityPlanning(new CapacityPlanningResultV1($status, self::at())));
        }
    }

    public function test_readiness_mapping_is_exhaustive(): void
    {
        foreach (OperationalReadinessStatusV1::cases() as $status) {
            $this->assertResponse($status, (new ReliabilityOperationsResponseFactory)->operationalReadiness(new OperationalReadinessResultV1($status, self::at())));
        }
    }

    private function assertResponse(BackedEnum $status, JsonResponse $response): void
    {
        $expected = match ($status->value) {
            'missing' => 404,
            'corrupted', 'dependency_unavailable' => 503,
            'available', 'degraded', 'healthy', 'unavailable', 'ready', 'at_risk', 'blocked', 'sufficient', 'exhausted' => 200,
            default => throw new UnexpectedValueException('Unknown Reliability Operations HTTP status.'),
        };
        self::assertSame($expected, $response->getStatusCode());
        self::assertSame(['status', 'observedAt'], array_keys($response->getData(true)));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    private static function at(): ReliabilityOperationsObservedAt
    {
        return new ReliabilityOperationsObservedAt(new DateTimeImmutable('2026-08-07T10:00:00.123456Z'));
    }
}
