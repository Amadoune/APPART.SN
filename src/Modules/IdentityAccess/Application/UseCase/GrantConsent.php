<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use DateTimeImmutable;

final readonly class GrantConsent extends AccountAction
{
    public function execute(AccountId $id, ConsentPurpose $purpose, DateTimeImmutable $at): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->grantConsent($purpose, $at);

        return $this->save($account, $expectedVersion);
    }
}
