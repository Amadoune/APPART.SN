<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class SuspendAccount extends AccountAction
{
    public function execute(AccountId $id, DateTimeImmutable $at): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->suspend($at);

        return $this->save($account, $expectedVersion);
    }
}
