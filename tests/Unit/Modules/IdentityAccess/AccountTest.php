<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Domain\Event\AccountReactivated;
use Appart\Modules\IdentityAccess\Domain\Event\AccountRegistered;
use Appart\Modules\IdentityAccess\Domain\Event\AccountSuspended;
use Appart\Modules\IdentityAccess\Domain\Event\ConsentGranted;
use Appart\Modules\IdentityAccess\Domain\Event\ConsentWithdrawn;
use Appart\Modules\IdentityAccess\Domain\Event\EmailVerified;
use Appart\Modules\IdentityAccess\Domain\Event\PasswordChanged;
use Appart\Modules\IdentityAccess\Domain\Event\PhoneVerified;
use Appart\Modules\IdentityAccess\Domain\Event\RoleGranted;
use Appart\Modules\IdentityAccess\Domain\Event\RoleRevoked;
use Appart\Modules\IdentityAccess\Domain\Exception\AccountStateViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\ConsentViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\PasswordUnchanged;
use Appart\Modules\IdentityAccess\Domain\Exception\RoleAssignmentViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\VerificationFailed;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function test_it_registers_an_active_unverified_account(): void
    {
        $account = $this->account();
        self::assertSame('contact@appart.sn', $account->email()->value);
        self::assertSame('+221771234567', $account->phone()->value);
        self::assertFalse($account->isSuspended());
        self::assertFalse($account->isEmailVerified());
        self::assertFalse($account->isPhoneVerified());
        self::assertInstanceOf(AccountRegistered::class, $account->releaseEvents()[0]);
    }

    public function test_it_changes_the_password_hash(): void
    {
        $account = $this->account();
        $account->releaseEvents();
        $account->changePassword($this->hash('new'), $this->now());
        self::assertTrue($account->passwordMatches($this->hash('new')));
        self::assertInstanceOf(PasswordChanged::class, $account->releaseEvents()[0]);
    }

    public function test_it_rejects_the_current_password_hash(): void
    {
        $account = $this->account();
        $this->expectException(PasswordUnchanged::class);
        $account->changePassword($this->hash('initial'), $this->now());
    }

    public function test_it_suspends_and_reactivates_an_account(): void
    {
        $account = $this->account();
        $account->releaseEvents();
        $account->suspend($this->now());
        self::assertTrue($account->isSuspended());
        self::assertInstanceOf(AccountSuspended::class, $account->releaseEvents()[0]);
        $account->reactivate($this->now());
        self::assertFalse($account->isSuspended());
        self::assertInstanceOf(AccountReactivated::class, $account->releaseEvents()[0]);
    }

    public function test_it_rejects_repeated_suspension(): void
    {
        $account = $this->account();
        $account->suspend($this->now());
        $this->expectException(AccountStateViolation::class);
        $account->suspend($this->now());
    }

    public function test_it_rejects_reactivation_of_an_active_account(): void
    {
        $this->expectException(AccountStateViolation::class);
        $this->account()->reactivate($this->now());
    }

    public function test_it_verifies_email_with_the_expected_token(): void
    {
        $account = $this->account();
        $account->releaseEvents();
        $account->verifyEmail($this->emailToken(), $this->now());
        self::assertTrue($account->isEmailVerified());
        self::assertInstanceOf(EmailVerified::class, $account->releaseEvents()[0]);
    }

    public function test_it_verifies_phone_with_the_expected_token(): void
    {
        $account = $this->account();
        $account->releaseEvents();
        $account->verifyPhone($this->phoneToken(), $this->now());
        self::assertTrue($account->isPhoneVerified());
        self::assertInstanceOf(PhoneVerified::class, $account->releaseEvents()[0]);
    }

    public function test_it_rejects_an_invalid_verification_token(): void
    {
        $this->expectException(VerificationFailed::class);
        $this->account()->verifyEmail(VerificationToken::forChannel(VerificationChannel::Email, str_repeat('x', 40)), $this->now());
    }

    public function test_it_rejects_an_expired_verification_token(): void
    {
        $this->expectException(VerificationFailed::class);
        $this->account()->verifyEmail($this->emailToken(), $this->now()->modify('+2 hours'));
    }

    public function test_it_rejects_reusing_a_verification_token(): void
    {
        $account = $this->account();
        $account->verifyEmail($this->emailToken(), $this->now());
        $this->expectException(VerificationFailed::class);
        $account->verifyEmail($this->emailToken(), $this->now());
    }

    public function test_a_phone_token_cannot_verify_email(): void
    {
        $phoneTokenWithSameSecret = VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('e', 40));
        $this->expectException(VerificationFailed::class);
        $this->account()->verifyEmail($phoneTokenWithSameSecret, $this->now());
    }

    public function test_replacing_a_token_invalidates_the_previous_token(): void
    {
        $account = $this->account();
        $replacement = VerificationToken::forChannel(VerificationChannel::Email, str_repeat('n', 40));
        $account->replaceEmailVerification($replacement, $this->now()->modify('+10 minutes'), $this->now()->modify('+2 hours'));

        $this->expectException(VerificationFailed::class);
        $account->verifyEmail($this->emailToken(), $this->now()->modify('+11 minutes'));
    }

    public function test_a_replacement_token_can_be_consumed_only_once(): void
    {
        $account = $this->account();
        $replacement = VerificationToken::forChannel(VerificationChannel::Email, str_repeat('n', 40));
        $account->replaceEmailVerification($replacement, $this->now()->modify('+10 minutes'), $this->now()->modify('+2 hours'));
        $account->verifyEmail($replacement, $this->now()->modify('+11 minutes'));

        $this->expectException(VerificationFailed::class);
        $account->verifyEmail($replacement, $this->now()->modify('+12 minutes'));
    }

    public function test_it_grants_and_revokes_a_role(): void
    {
        $account = $this->account();
        $role = RoleId::fromString('particulier');
        $account->releaseEvents();
        $account->grantRole($role, $this->now());
        self::assertTrue($account->hasRole($role));
        self::assertInstanceOf(RoleGranted::class, $account->releaseEvents()[0]);
        $account->revokeRole($role, $this->now());
        self::assertFalse($account->hasRole($role));
        self::assertInstanceOf(RoleRevoked::class, $account->releaseEvents()[0]);
    }

    public function test_it_rejects_duplicate_role_assignment(): void
    {
        $account = $this->account();
        $role = RoleId::fromString('particulier');
        $account->grantRole($role, $this->now());
        $this->expectException(RoleAssignmentViolation::class);
        $account->grantRole($role, $this->now());
    }

    public function test_it_rejects_revoking_an_unassigned_role(): void
    {
        $this->expectException(RoleAssignmentViolation::class);
        $this->account()->revokeRole(RoleId::fromString('particulier'), $this->now());
    }

    public function test_suspension_neutralizes_roles_and_blocks_sensitive_changes(): void
    {
        $account = $this->account();
        $role = RoleId::fromString('particulier');
        $account->grantRole($role, $this->now());
        $account->suspend($this->now());
        self::assertFalse($account->hasRole($role));
        $this->expectException(AccountStateViolation::class);
        $account->changePassword($this->hash('new'), $this->now());
    }

    public function test_suspension_revokes_roles_and_reactivation_does_not_restore_them(): void
    {
        $account = $this->account();
        $role = RoleId::fromString('particulier');
        $account->grantRole($role, $this->now());
        $account->suspend($this->now());
        $account->reactivate($this->now());
        self::assertFalse($account->hasRole($role));
    }

    public function test_it_records_and_withdraws_consent(): void
    {
        $account = $this->account();
        $purpose = ConsentPurpose::fromString('marketing_email');
        $account->releaseEvents();
        $account->grantConsent($purpose, $this->now());
        self::assertTrue($account->hasConsent($purpose));
        self::assertInstanceOf(ConsentGranted::class, $account->releaseEvents()[0]);
        $account->withdrawConsent($purpose, $this->now());
        self::assertFalse($account->hasConsent($purpose));
        self::assertInstanceOf(ConsentWithdrawn::class, $account->releaseEvents()[0]);
    }

    public function test_it_rejects_duplicate_consent(): void
    {
        $account = $this->account();
        $purpose = ConsentPurpose::fromString('marketing_email');
        $account->grantConsent($purpose, $this->now());
        $this->expectException(ConsentViolation::class);
        $account->grantConsent($purpose, $this->now());
    }

    public function test_release_events_preserves_order_and_empties_the_collection(): void
    {
        $account = $this->account();
        $account->suspend($this->now());
        $events = $account->releaseEvents();

        self::assertInstanceOf(AccountRegistered::class, $events[0]);
        self::assertInstanceOf(AccountSuspended::class, $events[1]);
        self::assertSame([], $account->releaseEvents());
    }

    public function test_events_never_contain_a_hash_or_verification_token(): void
    {
        $account = $this->account();
        $serializedEvents = serialize($account->releaseEvents());

        self::assertStringNotContainsString('salt-initial', $serializedEvents);
        self::assertStringNotContainsString(str_repeat('e', 40), $serializedEvents);
        self::assertStringNotContainsString(str_repeat('p', 40), $serializedEvents);
        self::assertStringNotContainsString('contact@appart.sn', strtolower($serializedEvents));
        self::assertStringNotContainsString('771234567', $serializedEvents);
        self::assertStringNotContainsString('Awa Ndiaye', $serializedEvents);
    }

    public function test_registration_event_contains_only_identity_time_and_ordering_metadata(): void
    {
        $event = $this->account()->releaseEvents()[0];

        self::assertInstanceOf(AccountRegistered::class, $event);
        self::assertSame(0, $event->aggregateVersion());
        self::assertSame(1, $event->eventIndex());
        self::assertSame($this->id()->value, $event->accountId()->value);
        self::assertEquals($this->now(), $event->occurredAt());
    }

    private function account(): Account
    {
        return Account::register($this->id(), EmailAddress::fromString('Contact@APPART.SN'), PhoneNumber::fromString('+221 77 123 45 67'), PersonName::fromString('Awa Ndiaye'), $this->hash('initial'), $this->emailToken(), $this->phoneToken(), $this->now()->modify('+1 hour'), $this->now());
    }

    private function id(): AccountId
    {
        return AccountId::fromString('10000000-0000-4000-8000-000000000001');
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
