<?php

namespace App\Http\Controllers;

use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventOrchestrator;
use App\Http\AccountStatusHttpResultPresenter;
use App\Http\Requests\AccountStatusTransitionHttpRequest;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Illuminate\Http\JsonResponse;
use Throwable;

final class AccountStatusHttpController extends Controller
{
    public function __construct(
        private readonly AccountStatusAtomicEventOrchestrator $orchestrator,
        private readonly AccountStatusHttpResultPresenter $presenter,
    ) {}

    public function suspend(AccountStatusTransitionHttpRequest $request): JsonResponse
    {
        return $this->transition($request, AccountStatusAction::Suspend);
    }

    public function reactivate(AccountStatusTransitionHttpRequest $request): JsonResponse
    {
        return $this->transition($request, AccountStatusAction::Reactivate);
    }

    private function transition(
        AccountStatusTransitionHttpRequest $request,
        AccountStatusAction $action,
    ): JsonResponse {
        try {
            return $this->presenter->response(
                $this->orchestrator->transition($request->applicationRequest($action)),
            );
        } catch (Throwable) {
            return $this->presenter->unavailable();
        }
    }
}
