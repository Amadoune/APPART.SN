<?php

namespace Appart\Modules\IdentityAccess\Application\UseCase;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;

final readonly class RegisterAccount
{
    public function __construct(private AccountRegistry $accounts) {}

    public function execute(AccountId $id, EmailAddress $email, PhoneNumber $phone, PersonName $name, PasswordHash $hash, VerificationToken $emailToken, VerificationToken $phoneToken, DateTimeImmutable $expiresAt, DateTimeImmutable $registeredAt): Account
    {
        $account = Account::register($id, $email, $phone, $name, $hash, $emailToken, $phoneToken, $expiresAt, $registeredAt);
        $this->accounts->add($account);

        return $account;
    }
}
