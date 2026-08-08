<?php

namespace App\Http\LegacyMigration;

use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationCutoverStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationInventoryStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationQuarantineStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationReconciliationStatusV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveResultV1;
use Appart\Modules\LegacyMigration\Application\PublicRead\LegacyMigrationWaveStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class LegacyMigrationResponseFactory
{
    public function inventory(LegacyMigrationInventoryResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            LegacyMigrationInventoryStatusV1::Available => 200,
            LegacyMigrationInventoryStatusV1::Missing => 404,
            LegacyMigrationInventoryStatusV1::Corrupted,
            LegacyMigrationInventoryStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $result->observedAt, $status);
    }

    public function wave(LegacyMigrationWaveResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            LegacyMigrationWaveStatusV1::Ready,
            LegacyMigrationWaveStatusV1::Blocked,
            LegacyMigrationWaveStatusV1::Completed => 200,
            LegacyMigrationWaveStatusV1::Missing => 404,
            LegacyMigrationWaveStatusV1::Corrupted,
            LegacyMigrationWaveStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $result->observedAt, $status);
    }

    public function reconciliation(LegacyMigrationReconciliationResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            LegacyMigrationReconciliationStatusV1::Matched,
            LegacyMigrationReconciliationStatusV1::Divergent,
            LegacyMigrationReconciliationStatusV1::Pending => 200,
            LegacyMigrationReconciliationStatusV1::Missing => 404,
            LegacyMigrationReconciliationStatusV1::Corrupted,
            LegacyMigrationReconciliationStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $result->observedAt, $status);
    }

    public function quarantine(LegacyMigrationQuarantineResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            LegacyMigrationQuarantineStatusV1::Empty,
            LegacyMigrationQuarantineStatusV1::ContainsItems => 200,
            LegacyMigrationQuarantineStatusV1::Missing => 404,
            LegacyMigrationQuarantineStatusV1::Corrupted,
            LegacyMigrationQuarantineStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $result->observedAt, $status);
    }

    public function cutover(LegacyMigrationCutoverResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            LegacyMigrationCutoverStatusV1::Ready,
            LegacyMigrationCutoverStatusV1::Blocked,
            LegacyMigrationCutoverStatusV1::Completed => 200,
            LegacyMigrationCutoverStatusV1::Missing => 404,
            LegacyMigrationCutoverStatusV1::Corrupted,
            LegacyMigrationCutoverStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $result->observedAt, $status);
    }

    private function response(string $status, string $observedAt, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status, 'observedAt' => $observedAt], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
