<?php

namespace App\Http\Controllers;

use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Http\MediaItemLifecycleHttpResultMapper;
use App\Http\Requests\MediaItemLifecycleTransitionRequest;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Illuminate\Http\JsonResponse;
use Throwable;

final class MediaItemLifecycleHttpController extends Controller
{
    public function __construct(
        private readonly MediaItemLifecycleAtomicEventOrchestrator $orchestrator,
        private readonly MediaItemLifecycleHttpResultMapper $mapper,
    ) {}

    public function __invoke(MediaItemLifecycleTransitionRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator->transition($request->applicationRequest());
        } catch (Throwable) {
            $result = new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted);
        }

        return $this->mapper->response($result);
    }
}
