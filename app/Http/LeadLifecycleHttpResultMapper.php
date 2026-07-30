<?php

namespace App\Http;

use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class LeadLifecycleHttpResultMapper
{
    public function response(LeadLifecycleOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            LeadLifecycleOrchestrationStatus::Applied => $this->json($result, 200),
            LeadLifecycleOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            LeadLifecycleOrchestrationStatus::Missing => $this->json($result, 404),
            LeadLifecycleOrchestrationStatus::VersionConflict => $this->json($result, 409),
            LeadLifecycleOrchestrationStatus::Denied => $this->json($result, 422),
            LeadLifecycleOrchestrationStatus::StateConflict => $this->json($result, 409),
            LeadLifecycleOrchestrationStatus::ContextDivergence => $this->json($result, 409),
            LeadLifecycleOrchestrationStatus::PersistenceCorrupted => $this->json($result, 503),
        };
    }

    private function json(LeadLifecycleOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->diagnostic?->value,
        ], $status);
    }
}
