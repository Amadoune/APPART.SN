<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;

final class AccountTestData
{
    public static function id(string $suffix = '000000000001'): AccountId
    {
        return AccountId::fromString('41000000-0000-4000-8000-'.$suffix);
    }

    public static function account(string $suffix = '000000000001', bool $suspended = false): Account
    {
        $at = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        $account = Account::register(
            self::id($suffix),
            EmailAddress::fromString('account-'.$suffix.'@example.test'),
            PhoneNumber::fromString('+221770'.substr($suffix, -6)),
            PersonName::fromString('Account Test'),
            PasswordHash::fromString('$generic$v=1$salt-test$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $at->modify('+1 hour'),
            $at,
        );
        $account->releaseEvents();
        if ($suspended) {
            $account->suspend($at->modify('+1 minute'));
            $account->releaseEvents();
        }

        return $account;
    }
}
