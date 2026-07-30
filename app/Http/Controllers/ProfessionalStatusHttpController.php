<?php

namespace App\Http\Controllers;

use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Http\ProfessionalStatusHttpResultMapper;
use App\Http\Requests\ProfessionalStatusTransitionRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ProfessionalStatusHttpController extends Controller
{
    public function __construct(
        private readonly ProfessionalStatusAtomicEventOrchestrator $orchestrator,
        private readonly ProfessionalStatusHttpResultMapper $mapper,
    ) {}

    public function __invoke(ProfessionalStatusTransitionRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator->transition($request->applicationRequest());
        } catch (Throwable) {
            $result = new ProfessionalStatusOrchestrationResult(ProfessionalStatusOrchestrationStatus::PersistenceCorrupted);
        }

        return $this->mapper->response($result);
    }
}
