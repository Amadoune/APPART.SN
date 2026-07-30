<?php

namespace App\Http;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class ReservationLifecycleHttpResultMapper
{
    public function response(ReservationLifecycleOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            ReservationLifecycleOrchestrationStatus::Applied => $this->json($result, 200),
            ReservationLifecycleOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            ReservationLifecycleOrchestrationStatus::Missing => $this->json($result, 404),
            ReservationLifecycleOrchestrationStatus::VersionConflict => $this->json($result, 409),
            ReservationLifecycleOrchestrationStatus::StateConflict => $this->json($result, 409),
            ReservationLifecycleOrchestrationStatus::Denied => $this->json($result, 422),
            ReservationLifecycleOrchestrationStatus::PersistenceCorrupted => $this->json($result, 503),
        };
    }

    private function json(ReservationLifecycleOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->diagnostic?->code->value,
        ], $status);
    }
}
