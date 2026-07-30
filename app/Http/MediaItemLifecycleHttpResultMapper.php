<?php

namespace App\Http;

use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final readonly class MediaItemLifecycleHttpResultMapper
{
    public function response(MediaItemLifecycleOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            MediaItemLifecycleOrchestrationStatus::Applied => $this->json($result, 200),
            MediaItemLifecycleOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            MediaItemLifecycleOrchestrationStatus::Missing => $this->json($result, 404),
            MediaItemLifecycleOrchestrationStatus::VersionConflict => $this->json($result, 409),
            MediaItemLifecycleOrchestrationStatus::Denied => $this->json($result, 422),
            MediaItemLifecycleOrchestrationStatus::StateConflict => $this->json($result, 409),
            MediaItemLifecycleOrchestrationStatus::ContextDivergence => $this->json($result, 409),
            MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted => $this->json($result, 503),
        };
    }

    private function json(MediaItemLifecycleOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->diagnostic?->value,
        ], $status);
    }
}
