<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

final readonly class AccountStatusTransition
{
    public function __construct(
        public AccountStatusState $from,
        public AccountStatusAction $action,
        public AccountStatusState $to,
    ) {}
}
