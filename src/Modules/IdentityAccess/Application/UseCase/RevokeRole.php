<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;

final readonly class RevokeRole extends AccountAction
{
    public function execute(AccountId $id, RoleId $role, DateTimeImmutable $at): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->revokeRole($role, $at);

        return $this->save($account, $expectedVersion);
    }
}
