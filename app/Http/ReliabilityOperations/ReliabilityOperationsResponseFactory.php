<?php

namespace App\Http\ReliabilityOperations;

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
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class ReliabilityOperationsResponseFactory
{
    public function observability(ObservabilityResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            ObservabilityStatusV1::Available, ObservabilityStatusV1::Degraded => 200,
            ObservabilityStatusV1::Missing => 404,
            ObservabilityStatusV1::Corrupted, ObservabilityStatusV1::DependencyUnavailable => 503,
        });
    }

    public function serviceHealth(ServiceHealthResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            ServiceHealthStatusV1::Healthy, ServiceHealthStatusV1::Degraded, ServiceHealthStatusV1::Unavailable => 200,
            ServiceHealthStatusV1::Missing => 404,
            ServiceHealthStatusV1::Corrupted, ServiceHealthStatusV1::DependencyUnavailable => 503,
        });
    }

    public function alerting(AlertingResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            AlertingStatusV1::Ready, AlertingStatusV1::Degraded, AlertingStatusV1::Unavailable => 200,
            AlertingStatusV1::Missing => 404,
            AlertingStatusV1::Corrupted, AlertingStatusV1::DependencyUnavailable => 503,
        });
    }

    public function maintenanceOperations(MaintenanceOperationsResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            MaintenanceOperationsStatusV1::Ready, MaintenanceOperationsStatusV1::Degraded, MaintenanceOperationsStatusV1::Blocked => 200,
            MaintenanceOperationsStatusV1::Missing => 404,
            MaintenanceOperationsStatusV1::Corrupted, MaintenanceOperationsStatusV1::DependencyUnavailable => 503,
        });
    }

    public function continuity(ContinuityResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            ContinuityStatusV1::Ready, ContinuityStatusV1::AtRisk, ContinuityStatusV1::Blocked => 200,
            ContinuityStatusV1::Missing => 404,
            ContinuityStatusV1::Corrupted, ContinuityStatusV1::DependencyUnavailable => 503,
        });
    }

    public function capacityPlanning(CapacityPlanningResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            CapacityPlanningStatusV1::Sufficient, CapacityPlanningStatusV1::AtRisk, CapacityPlanningStatusV1::Exhausted => 200,
            CapacityPlanningStatusV1::Missing => 404,
            CapacityPlanningStatusV1::Corrupted, CapacityPlanningStatusV1::DependencyUnavailable => 503,
        });
    }

    public function operationalReadiness(OperationalReadinessResultV1 $result): JsonResponse
    {
        return $this->response($result->status->value, $result->observedAt, match ($result->status) {
            OperationalReadinessStatusV1::Ready, OperationalReadinessStatusV1::AtRisk, OperationalReadinessStatusV1::Blocked => 200,
            OperationalReadinessStatusV1::Missing => 404,
            OperationalReadinessStatusV1::Corrupted, OperationalReadinessStatusV1::DependencyUnavailable => 503,
        });
    }

    private function response(string $status, string $observedAt, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'observedAt' => $observedAt], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
