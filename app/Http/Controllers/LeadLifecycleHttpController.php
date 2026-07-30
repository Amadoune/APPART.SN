<?php

namespace App\Http\Controllers;

use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Http\LeadLifecycleHttpResultMapper;
use App\Http\Requests\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;
use Throwable;

final class LeadLifecycleHttpController extends Controller
{
    public function __construct(
        private readonly LeadLifecycleAtomicEventOrchestrator $orchestrator,
        private readonly LeadLifecycleHttpResultMapper $mapper,
    ) {}

    public function __invoke(LeadLifecycleTransitionRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator->transition($request->applicationRequest());
        } catch (Throwable) {
            $result = new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::PersistenceCorrupted);
        }

        return $this->mapper->response($result);
    }
}
