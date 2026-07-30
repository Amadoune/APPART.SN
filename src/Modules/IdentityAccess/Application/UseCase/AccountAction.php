<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Exception\AccountNotFound;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

abstract readonly class AccountAction
{
    public function __construct(protected AccountRegistry $accounts) {}

    protected function account(AccountId $id): Account
    {
        return $this->accounts->find($id) ?? throw new AccountNotFound;
    }

    protected function save(Account $account, int $expectedVersion): Account
    {
        $this->accounts->save($account, $expectedVersion);

        return $account;
    }
}
