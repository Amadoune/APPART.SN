<?php

namespace App\Http\Controllers;

use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventOrchestrator;
use App\Http\PlaceLifecycleHttpResultMapper;
use App\Http\Requests\PlaceLifecycleTransitionRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

final class PlaceLifecycleHttpController extends Controller
{
    public function __construct(
        private readonly PlaceLifecycleAtomicEventOrchestrator $orchestrator,
        private readonly PlaceLifecycleHttpResultMapper $mapper,
    ) {}

    public function __invoke(PlaceLifecycleTransitionRequest $request): JsonResponse
    {
        try {
            return $this->mapper->response(
                $this->orchestrator->transition($request->applicationRequest()),
            );
        } catch (Throwable) {
            return $this->mapper->atomicFailure();
        }
    }
}
