<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Application\UseCase\ChangePassword;
use Appart\Modules\IdentityAccess\Application\UseCase\GrantRole;
use Appart\Modules\IdentityAccess\Application\UseCase\ReactivateAccount;
use Appart\Modules\IdentityAccess\Application\UseCase\RegisterAccount;
use Appart\Modules\IdentityAccess\Application\UseCase\RevokeRole;
use Appart\Modules\IdentityAccess\Application\UseCase\SuspendAccount;
use Appart\Modules\IdentityAccess\Application\UseCase\VerifyEmail;
use Appart\Modules\IdentityAccess\Application\UseCase\VerifyPhone;
use Appart\Modules\IdentityAccess\Domain\Exception\AccountNotFound;
use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\Exception\DuplicateAccountIdentity;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Modules\IdentityAccess\Support\FakeAccountRegistry;

final class IdentityAccessUseCasesTest extends TestCase
{
    public function test_registration_atomically_adds_an_account(): void
    {
        $registry = new FakeAccountRegistry;
        $account = $this->register($registry);
        $stored = $registry->find($account->id());
        self::assertNotSame($account, $stored);
        self::assertTrue($stored?->id()->equals($account->id()));
    }

    public function test_registration_rejects_a_duplicate_identifier(): void
    {
        $registry = new FakeAccountRegistry;
        $this->register($registry);
        $this->expectException(DuplicateAccountIdentity::class);
        $this->register($registry);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        $registry = new FakeAccountRegistry;
        $this->register($registry);
        $this->expectException(DuplicateAccountIdentity::class);
        $this->register($registry, '10000000-0000-4000-8000-000000000002', 'contact@appart.sn', '+221771234568');
    }

    public function test_registration_rejects_a_duplicate_phone(): void
    {
        $registry = new FakeAccountRegistry;
        $this->register($registry);
        $this->expectException(DuplicateAccountIdentity::class);
        $this->register($registry, '10000000-0000-4000-8000-000000000002', 'other@appart.sn', '+221771234567');
    }

    public function test_account_use_cases_orchestrate_the_aggregate(): void
    {
        $registry = new FakeAccountRegistry;
        $account = $this->register($registry);
        $role = RoleId::fromString('particulier');
        (new VerifyEmail($registry))->execute($account->id(), $this->emailToken(), $this->now());
        (new VerifyPhone($registry))->execute($account->id(), $this->phoneToken(), $this->now());
        (new ChangePassword($registry))->execute($account->id(), $this->hash('new'), $this->now());
        (new GrantRole($registry))->execute($account->id(), $role, $this->now());
        (new RevokeRole($registry))->execute($account->id(), $role, $this->now());
        (new SuspendAccount($registry))->execute($account->id(), $this->now());
        (new ReactivateAccount($registry))->execute($account->id(), $this->now());
        $stored = $registry->find($account->id());
        self::assertTrue($stored?->isEmailVerified());
        self::assertTrue($stored?->isPhoneVerified());
        self::assertFalse($stored?->isSuspended());
    }

    public function test_an_action_rejects_an_unknown_account(): void
    {
        $this->expectException(AccountNotFound::class);
        (new SuspendAccount(new FakeAccountRegistry))->execute(AccountId::fromString('10000000-0000-4000-8000-000000000099'), $this->now());
    }

    public function test_a_failed_save_does_not_leak_the_mutation_into_the_registry(): void
    {
        $registry = new FakeAccountRegistry;
        $account = $this->register($registry);
        $registry->failNextSave();

        try {
            (new ChangePassword($registry))->execute($account->id(), $this->hash('new'), $this->now());
            self::fail('A concurrent modification was expected.');
        } catch (ConcurrentAccountModification) {
            self::assertTrue($registry->find($account->id())?->passwordMatches($this->hash('initial')));
            self::assertFalse($registry->find($account->id())?->passwordMatches($this->hash('new')));
        }
    }

    public function test_a_stale_aggregate_is_rejected_as_a_concurrent_conflict(): void
    {
        $registry = new FakeAccountRegistry;
        $registered = $this->register($registry);
        $first = $registry->find($registered->id());
        $stale = $registry->find($registered->id());
        self::assertNotNull($first);
        self::assertNotNull($stale);
        $expectedVersion = $first->version();
        $first->suspend($this->now());
        $registry->save($first, $expectedVersion);
        $stale->changePassword($this->hash('new'), $this->now());

        $this->expectException(ConcurrentAccountModification::class);
        $registry->save($stale, $expectedVersion);
    }

    private function register(FakeAccountRegistry $registry, string $id = '10000000-0000-4000-8000-000000000001', string $email = 'contact@appart.sn', string $phone = '+221771234567'): Account
    {
        return (new RegisterAccount($registry))->execute(AccountId::fromString($id), EmailAddress::fromString($email), PhoneNumber::fromString($phone), PersonName::fromString('Awa Ndiaye'), $this->hash('initial'), $this->emailToken(), $this->phoneToken(), $this->now()->modify('+1 hour'), $this->now());
    }

    private function hash(string $suffix): PasswordHash
    {
        return PasswordHash::fromString('$generic$v=1$salt-'.$suffix.'$'.str_repeat('x', 40));
    }

    private function emailToken(): VerificationToken
    {
        return VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40));
    }

    private function phoneToken(): VerificationToken
    {
        return VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40));
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-16T12:00:00+00:00');
    }
}
