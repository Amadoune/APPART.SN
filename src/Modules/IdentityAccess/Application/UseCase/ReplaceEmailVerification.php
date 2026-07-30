<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;

final readonly class ReplaceEmailVerification extends AccountAction
{
    public function execute(AccountId $id, VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): Account
    {
        $account = $this->account($id);
        $expectedVersion = $account->version();
        $account->replaceEmailVerification($token, $issuedAt, $expiresAt);

        return $this->save($account, $expectedVersion);
    }
}
