<?php

namespace Tests\Unit\Application;

use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountAvailabilityPurpose;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountAvailabilityStatus;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureReadResult;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\AccountClosureState;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountClosureStateReader;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\DeterministicAccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusPersistenceReadResult;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\AccountStatusStoredState;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccountAvailabilityPolicyTest extends TestCase
{
    /**
     * @return iterable<string, array{AccountStatusState, AccountClosureReadResult, AccountAvailabilityPurpose, AccountAvailabilityStatus}>
     */
    public static function decisions(): iterable
    {
        yield 'open active authenticates' => [
            AccountStatusState::Active,
            AccountClosureReadResult::legacyOpen(),
            AccountAvailabilityPurpose::Authenticate,
            AccountAvailabilityStatus::Available,
        ];
        yield 'suspension wins over reopen' => [
            AccountStatusState::Suspended,
            AccountClosureReadResult::found(AccountClosureState::Closed, 2),
            AccountAvailabilityPurpose::ReopenClosure,
            AccountAvailabilityStatus::UnavailableSuspended,
        ];
        yield 'closed denies ordinary access' => [
            AccountStatusState::Active,
            AccountClosureReadResult::found(AccountClosureState::Closed, 2),
            AccountAvailabilityPurpose::RenewSession,
            AccountAvailabilityStatus::UnavailableClosed,
        ];
        yield 'closed can enter reopen use case' => [
            AccountStatusState::Active,
            AccountClosureReadResult::found(AccountClosureState::Closed, 2),
            AccountAvailabilityPurpose::ReopenClosure,
            AccountAvailabilityStatus::Available,
        ];
        yield 'reopen on open state is inconsistent' => [
            AccountStatusState::Active,
            AccountClosureReadResult::legacyOpen(),
            AccountAvailabilityPurpose::ReopenClosure,
            AccountAvailabilityStatus::Inconsistent,
        ];
    }

    #[Test]
    #[DataProvider('decisions')]
    public function it_applies_the_closed_fail_closed_policy(
        AccountStatusState $accountStatus,
        AccountClosureReadResult $closure,
        AccountAvailabilityPurpose $purpose,
        AccountAvailabilityStatus $expected,
    ): void {
        $accountId = AccountId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
        $accounts = $this->createStub(AccountRegistry::class);
        $accounts->method('find')->willReturn($this->account($accountId));
        $statuses = $this->createStub(AccountStatusWorkflowStore::class);
        $statuses->method('read')->willReturn(AccountStatusPersistenceReadResult::found(
            new AccountStatusStoredState($accountId, $accountStatus, new AccountStatusVersion(4)),
        ));
        $closures = $this->createStub(AccountClosureStateReader::class);
        $closures->method('read')->willReturn($closure);

        $result = (new DeterministicAccountAvailabilityInspector($accounts, $statuses, $closures))
            ->inspect($accountId, $purpose, new DateTimeImmutable('2026-01-01T00:00:00Z'));

        self::assertSame($expected, $result->status);
        self::assertSame(4, $result->accountStatusVersion);
        self::assertSame($closure->version, $result->closureVersion);
    }

    #[Test]
    public function unavailable_or_corrupted_sources_fail_closed(): void
    {
        $accountId = AccountId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
        $accounts = $this->createStub(AccountRegistry::class);
        $accounts->method('find')->willReturn(null);
        $statuses = $this->createStub(AccountStatusWorkflowStore::class);
        $statuses->method('read')->willReturn(AccountStatusPersistenceReadResult::persistenceRejected($accountId));
        $closures = $this->createStub(AccountClosureStateReader::class);
        $closures->method('read')->willReturn(AccountClosureReadResult::legacyOpen());

        $result = (new DeterministicAccountAvailabilityInspector($accounts, $statuses, $closures))
            ->inspect($accountId, AccountAvailabilityPurpose::Authenticate, new DateTimeImmutable);

        self::assertSame(AccountAvailabilityStatus::Indeterminate, $result->status);
        self::assertFalse($result->isAvailable());
    }

    #[Test]
    public function a_consistently_missing_account_is_not_enumerated_as_available(): void
    {
        $accountId = AccountId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
        $accounts = $this->createStub(AccountRegistry::class);
        $accounts->method('find')->willReturn(null);
        $statuses = $this->createStub(AccountStatusWorkflowStore::class);
        $statuses->method('read')->willReturn(AccountStatusPersistenceReadResult::accountMissing($accountId));
        $closures = $this->createStub(AccountClosureStateReader::class);
        $closures->method('read')->willReturn(AccountClosureReadResult::legacyOpen());

        $result = (new DeterministicAccountAvailabilityInspector($accounts, $statuses, $closures))
            ->inspect($accountId, AccountAvailabilityPurpose::RecoverPassword, new DateTimeImmutable);

        self::assertSame(AccountAvailabilityStatus::AccountMissing, $result->status);
        self::assertFalse($result->isAvailable());
    }

    private function account(AccountId $accountId): Account
    {
        $registeredAt = new DateTimeImmutable('2025-01-01T00:00:00Z');

        return Account::register(
            $accountId,
            EmailAddress::fromString('availability@example.test'),
            PhoneNumber::fromString('+221770000099'),
            PersonName::fromString('Availability Account'),
            PasswordHash::fromString('$argon2id$v=19$m=65536,t=4,p=1$c2FsdA$ZGlnaWVzdA'),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 32)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 32)),
            new DateTimeImmutable('2025-01-01T01:00:00Z'),
            $registeredAt,
        );
    }
}
