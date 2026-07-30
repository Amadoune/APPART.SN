<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class AccountStatusCurrentState
{
    public function __construct(
        public AccountId $accountId,
        public AccountStatusState $state,
        public AccountStatusVersion $observedVersion,
    ) {}
}
