<?php

namespace Appart\Modules\IdentityAccess\Application\AuthenticationAuthority\Contract;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

interface CredentialSourceV1
{
    public function credential(AccountId $accountId): ?CredentialRecord;
}
