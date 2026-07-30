<?php

namespace App\Http\Controllers;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Http\Requests\ListingPublicationTransitionHttpRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationStatus;
use Illuminate\Http\JsonResponse;

final class ListingPublicationTransitionController extends Controller
{
    public function __construct(private readonly ListingPublicationEventOrchestrator $orchestrator) {}

    public function __invoke(ListingPublicationTransitionHttpRequest $request): JsonResponse
    {
        return $this->response($this->orchestrator->transition($request->applicationRequest()));
    }

    private function response(ListingPublicationOrchestrationResult $result): JsonResponse
    {
        return match ($result->status) {
            ListingPublicationOrchestrationStatus::Applied => $this->json($result, 200),
            ListingPublicationOrchestrationStatus::AlreadyApplied => $this->json($result, 200),
            ListingPublicationOrchestrationStatus::Denied => $this->json($result, 422),
            ListingPublicationOrchestrationStatus::ConcurrencyConflict => $this->json($result, 409),
            ListingPublicationOrchestrationStatus::PersistenceFailure => $this->json($result, 503),
        };
    }

    private function json(ListingPublicationOrchestrationResult $result, int $status): JsonResponse
    {
        return response()->json([
            'status' => $result->status->value,
            'diagnostic' => $result->workflowDiagnostic?->code->value ?? $result->orchestrationDiagnostic?->value,
        ], $status);
    }
}
