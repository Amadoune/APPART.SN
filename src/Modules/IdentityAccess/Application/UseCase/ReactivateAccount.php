<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class ReactivateAccount extends AccountAction
{
    public function execute(AccountId $id, DateTimeImmutable $at): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->reactivate($at);

        return $this->save($account, $expectedVersion);
    }
}
