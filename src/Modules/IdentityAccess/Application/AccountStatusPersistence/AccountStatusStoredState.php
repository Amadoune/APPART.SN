<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusPersistence;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class AccountStatusStoredState
{
    public function __construct(
        public AccountId $accountId,
        public AccountStatusState $state,
        public AccountStatusVersion $version,
    ) {}
}
