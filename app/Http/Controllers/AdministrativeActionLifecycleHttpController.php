<?php

namespace App\Http\Controllers;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Http\AdministrativeActionLifecycleHttpResultMapper;
use App\Http\Requests\AdministrativeActionLifecycleTransitionRequest;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;
use Throwable;

final class AdministrativeActionLifecycleHttpController extends Controller
{
    public function __construct(
        private readonly AdministrativeActionLifecycleAtomicEventOrchestrator $orchestrator,
        private readonly AdministrativeActionLifecycleHttpResultMapper $mapper,
    ) {}

    public function __invoke(
        AdministrativeActionLifecycleTransitionRequest $request,
    ): JsonResponse {
        try {
            $result = $this->orchestrator->transition($request->applicationRequest());
        } catch (Throwable) {
            $result = new AdministrativeActionLifecycleOrchestrationResult(
                AdministrativeActionLifecycleOrchestrationStatus::PersistenceCorrupted,
            );
        }

        return $this->mapper->response($result);
    }
}
