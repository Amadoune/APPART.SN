<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use DateTimeImmutable;

final readonly class ChangePassword extends AccountAction
{
    public function execute(AccountId $id, PasswordHash $hash, DateTimeImmutable $at): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->changePassword($hash, $at);

        return $this->save($account, $expectedVersion);
    }
}
