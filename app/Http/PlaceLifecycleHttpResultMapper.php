<?php

namespace App\Http;

use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationResult;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class PlaceLifecycleHttpResultMapper
{
    public function response(PlaceLifecycleOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            PlaceLifecycleOrchestrationStatus::Applied,
            PlaceLifecycleOrchestrationStatus::AlreadyApplied => $this->json(
                $result->status->value,
                $result->state->value,
                200,
            ),
            PlaceLifecycleOrchestrationStatus::InspectionMissing => $this->json(
                $result->status->value,
                $result->state->value,
                404,
            ),
            PlaceLifecycleOrchestrationStatus::ContextDivergence,
            PlaceLifecycleOrchestrationStatus::ReplayConflict,
            PlaceLifecycleOrchestrationStatus::SourceVersionConflict,
            PlaceLifecycleOrchestrationStatus::TargetVersionConflict,
            PlaceLifecycleOrchestrationStatus::StateConflict => $this->json(
                $result->status->value,
                $result->state->value,
                409,
            ),
            PlaceLifecycleOrchestrationStatus::WorkflowRefused,
            PlaceLifecycleOrchestrationStatus::TransitionRejected => $this->json(
                $result->status->value,
                $result->state->value,
                422,
            ),
            PlaceLifecycleOrchestrationStatus::InspectionCorrupted => $this->json(
                $result->status->value,
                $result->state->value,
                503,
            ),
        };
    }

    public function atomicFailure(): JsonResponse
    {
        return $this->json('atomic_integration_failure', null, 503);
    }

    private function json(string $status, ?string $state, int $httpStatus): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'state' => $state,
        ], $httpStatus);
    }
}
