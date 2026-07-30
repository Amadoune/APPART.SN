<?php

namespace Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureReadResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface AccountClosureStateReader
{
    public function read(AccountId $accountId): AccountClosureReadResult;
}
