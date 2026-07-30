<?php

namespace App\Http;

use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class ProfessionalStatusHttpResultMapper
{
    public function response(ProfessionalStatusOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            ProfessionalStatusOrchestrationStatus::Applied => $this->json($result, 200),
            ProfessionalStatusOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            ProfessionalStatusOrchestrationStatus::Missing => $this->json($result, 404),
            ProfessionalStatusOrchestrationStatus::VersionConflict => $this->json($result, 409),
            ProfessionalStatusOrchestrationStatus::Denied => $this->json($result, 422),
            ProfessionalStatusOrchestrationStatus::StateConflict => $this->json($result, 409),
            ProfessionalStatusOrchestrationStatus::ContextDivergence => $this->json($result, 409),
            ProfessionalStatusOrchestrationStatus::PersistenceCorrupted => $this->json($result, 503),
        };
    }

    private function json(ProfessionalStatusOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->diagnostic?->value,
        ], $status);
    }
}
