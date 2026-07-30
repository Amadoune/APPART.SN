<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;

final class ProfessionalStatusReplayPolicy
{
    public function classify(
        ProfessionalStatusAction $requestedAction,
        ProfessionalStatusTransitionContext $requestedContext,
        ProfessionalStatusContextualAppendInspection $inspected,
    ): ProfessionalStatusReplayOutcome {
        if ($inspected->version !== $requestedContext->expectedVersion->next()
            || $inspected->transition->action !== $requestedAction) {
            return ProfessionalStatusReplayOutcome::Conflict;
        }

        if ($inspected->actor != $requestedContext->actor
            || $inspected->occurredAt != $requestedContext->occurredAt) {
            return ProfessionalStatusReplayOutcome::ContextDivergence;
        }

        return ProfessionalStatusReplayOutcome::AlreadyApplied;
    }
}
