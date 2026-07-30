<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

final readonly class AccountStatusWorkflowResult
{
    private function __construct(
        public AccountStatusDecision $decision,
        public AccountStatusState $state,
        public ?AccountStatusTransition $transition,
    ) {}

    public static function applied(AccountStatusTransition $transition): self
    {
        return new self(AccountStatusDecision::Applied, $transition->to, $transition);
    }

    public static function refused(AccountStatusDecision $decision, AccountStatusState $state): self
    {
        return new self($decision, $state, null);
    }
}
