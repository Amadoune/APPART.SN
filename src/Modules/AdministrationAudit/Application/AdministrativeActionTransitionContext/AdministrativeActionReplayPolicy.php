<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;

final readonly class AdministrativeActionReplayPolicy
{
    public function classify(
        AdministrativeActionLifecycleAction $requestedAction,
        AdministrativeActionTransitionExecutionContext $requestedContext,
        AdministrativeActionContextualAppendInspection $inspected,
    ): AdministrativeActionReplayOutcome {
        if ($inspected->version !== $requestedContext->expectedVersion->next()) {
            return AdministrativeActionReplayOutcome::VersionConflict;
        }

        if ($inspected->transition->action !== $requestedAction
            || $requestedContext->decisionIdentities->action !== $requestedAction) {
            return AdministrativeActionReplayOutcome::TransitionDivergence;
        }

        if (! hash_equals($inspected->checksum->value, $requestedContext->checksum()->value)) {
            return AdministrativeActionReplayOutcome::ContextDivergence;
        }

        return AdministrativeActionReplayOutcome::AlreadyApplied;
    }
}
