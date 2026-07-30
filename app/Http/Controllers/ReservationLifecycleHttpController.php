<?php

namespace App\Http\Controllers;

use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Http\Requests\ReservationLifecycleTransitionRequest;
use App\Http\ReservationLifecycleHttpResultMapper;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationResult;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ReservationLifecycleHttpController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleAtomicEventOrchestrator $orchestrator,
        private readonly ReservationLifecycleHttpResultMapper $mapper,
    ) {}

    public function __invoke(ReservationLifecycleTransitionRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator->transition($request->applicationRequest());
        } catch (Throwable) {
            $result = ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }

        return $this->mapper->response($result);
    }
}
