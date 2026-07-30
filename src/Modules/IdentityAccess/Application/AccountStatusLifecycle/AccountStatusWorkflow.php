<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

final readonly class AccountStatusWorkflow
{
    public function initialState(): AccountStatusState
    {
        return AccountStatusState::Active;
    }

    public function decide(
        AccountStatusCurrentState $current,
        AccountStatusAction $action,
        AccountStatusContextV1 $context,
    ): AccountStatusWorkflowResult {
        if (! $context->isStructurallyValid()
            || ! $current->accountId->equals($context->accountId)
            || $current->state !== $context->currentState
            || ! $current->observedVersion->equals($context->observedVersion)
            || $action !== $context->action
        ) {
            return AccountStatusWorkflowResult::refused(AccountStatusDecision::InvalidContext, $current->state);
        }

        return match ($action) {
            AccountStatusAction::Suspend => match ($current->state) {
                AccountStatusState::Active => AccountStatusWorkflowResult::applied(
                    new AccountStatusTransition($current->state, $action, AccountStatusState::Suspended),
                ),
                AccountStatusState::Suspended => AccountStatusWorkflowResult::refused(
                    AccountStatusDecision::AlreadyInState,
                    $current->state,
                ),
            },
            AccountStatusAction::Reactivate => match ($current->state) {
                AccountStatusState::Active => AccountStatusWorkflowResult::refused(
                    AccountStatusDecision::AlreadyInState,
                    $current->state,
                ),
                AccountStatusState::Suspended => AccountStatusWorkflowResult::applied(
                    new AccountStatusTransition($current->state, $action, AccountStatusState::Active),
                ),
            },
        };
    }
}
