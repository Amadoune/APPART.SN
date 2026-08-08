<?php

namespace Tests\Feature;

use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;
use Tests\TestCase;

final class ReliabilityOperationsHttpFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(ObservabilityReaderV1::class, new class implements ObservabilityReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): ObservabilityResultV1
            {
                return new ObservabilityResultV1(ObservabilityStatusV1::Available, $observedAt);
            }
        });
        $this->app->instance(ServiceHealthReaderV1::class, new class implements ServiceHealthReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): ServiceHealthResultV1
            {
                return new ServiceHealthResultV1(ServiceHealthStatusV1::Healthy, $observedAt);
            }
        });
        $this->app->instance(AlertingReaderV1::class, new class implements AlertingReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): AlertingResultV1
            {
                return new AlertingResultV1(AlertingStatusV1::Ready, $observedAt);
            }
        });
        $this->app->instance(MaintenanceOperationsReaderV1::class, new class implements MaintenanceOperationsReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): MaintenanceOperationsResultV1
            {
                return new MaintenanceOperationsResultV1(MaintenanceOperationsStatusV1::Ready, $observedAt);
            }
        });
        $this->app->instance(ContinuityReaderV1::class, new class implements ContinuityReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): ContinuityResultV1
            {
                return new ContinuityResultV1(ContinuityStatusV1::Ready, $observedAt);
            }
        });
        $this->app->instance(CapacityPlanningReaderV1::class, new class implements CapacityPlanningReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): CapacityPlanningResultV1
            {
                return new CapacityPlanningResultV1(CapacityPlanningStatusV1::Sufficient, $observedAt);
            }
        });
        $this->app->instance(OperationalReadinessReaderV1::class, new class implements OperationalReadinessReaderV1
        {
            public function read(ReliabilityOperationsObservedAt $observedAt): OperationalReadinessResultV1
            {
                return new OperationalReadinessResultV1(OperationalReadinessStatusV1::Ready, $observedAt);
            }
        });
    }

    public function test_seven_public_endpoints_consume_only_public_readers(): void
    {
        $query = '?observedAt=2026-08-07T10%3A00%3A00.123456%2B00%3A00';
        $expected = ['observability' => 'available', 'service-health' => 'healthy', 'alerting' => 'ready', 'maintenance-operations' => 'ready', 'continuity' => 'ready', 'capacity-planning' => 'sufficient', 'operational-readiness' => 'ready'];
        foreach ($expected as $path => $status) {
            $this->getJson('/api/reliability-operations/'.$path.$query)->assertOk()->assertExactJson(['status' => $status, 'observedAt' => '2026-08-07T10:00:00.123456Z'])->assertHeader('Cache-Control', 'no-store, private')->assertHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    public function test_requests_reject_missing_and_unknown_inputs(): void
    {
        $this->getJson('/api/reliability-operations/observability')->assertUnprocessable();
        $this->getJson('/api/reliability-operations/continuity?observedAt=2026-08-07T10%3A00%3A00.123456%2B00%3A00&secret=value')->assertUnprocessable();
    }
}
