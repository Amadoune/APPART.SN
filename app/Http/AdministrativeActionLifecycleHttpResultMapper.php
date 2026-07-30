<?php

namespace App\Http;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class AdministrativeActionLifecycleHttpResultMapper
{
    public function response(
        AdministrativeActionLifecycleOrchestrationResult $result,
    ): JsonResponse {
        return match ($result->status) {
            AdministrativeActionLifecycleOrchestrationStatus::Applied => $this->json($result, 200),
            AdministrativeActionLifecycleOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            AdministrativeActionLifecycleOrchestrationStatus::Missing => $this->json($result, 404),
            AdministrativeActionLifecycleOrchestrationStatus::VersionConflict => $this->json($result, 409),
            AdministrativeActionLifecycleOrchestrationStatus::Denied => $this->json($result, 422),
            AdministrativeActionLifecycleOrchestrationStatus::StateConflict => $this->json($result, 409),
            AdministrativeActionLifecycleOrchestrationStatus::ContextDivergence => $this->json($result, 409),
            AdministrativeActionLifecycleOrchestrationStatus::TransitionDivergence => $this->json($result, 409),
            AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted => $this->json($result, 503),
        };
    }

    private function json(
        AdministrativeActionLifecycleOrchestrationResult $result,
        int $status,
    ): JsonResponse {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->diagnostic?->value,
        ], $status);
    }
}
