<?php

namespace App\Http\Controllers;

use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Http\Requests\PropertyLifecycleTransitionHttpRequest;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final class PropertyLifecycleTransitionController extends Controller
{
    public function __construct(private readonly PropertyLifecycleEventOrchestrator $orchestrator) {}

    public function __invoke(PropertyLifecycleTransitionHttpRequest $request): JsonResponse
    {
        return $this->response($this->orchestrator->transition($request->applicationRequest()));
    }

    private function response(PropertyLifecycleOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            PropertyLifecycleOrchestrationStatus::Applied => $this->json($result, 200),
            PropertyLifecycleOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            PropertyLifecycleOrchestrationStatus::Denied => $this->json($result, 422),
            PropertyLifecycleOrchestrationStatus::ConcurrencyConflict => $this->json($result, 409),
            PropertyLifecycleOrchestrationStatus::PersistenceFailure => $this->json($result, 503),
        };
    }

    private function json(PropertyLifecycleOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->workflowDiagnostic?->code->value ?? $result->orchestrationDiagnostic?->value,
        ], $status);
    }
}
