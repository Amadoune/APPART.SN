<?php

namespace Tests\Unit\IdentityAccess\ModeratorAuthorization;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\OwnerModeratorAuthorizationReaderV1;
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\Modules\IdentityAccess\Support\FakeAccountRegistry;

final class OwnerModeratorAuthorizationReaderV1Test extends TestCase
{
    #[Test]
    public function moderator_capabilities_are_allowed_only_for_an_active_moderator(): void
    {
        $registry = new FakeAccountRegistry;
        $account = $this->account(1);
        $account->grantRole(RoleId::fromString('moderator'), $this->now()->modify('+1 minute'));
        $registry->add($account);
        $reader = new OwnerModeratorAuthorizationReaderV1($registry);

        foreach ([
            ModerationCapabilityV1::Report,
            ModerationCapabilityV1::Validate,
            ModerationCapabilityV1::Investigate,
            ModerationCapabilityV1::Decide,
        ] as $capability) {
            self::assertSame(
                ModeratorAuthorizationDecisionV1::Allowed,
                $reader->authorize($account->id(), $capability, $this->now()),
            );
        }
        self::assertSame(
            ModeratorAuthorizationDecisionV1::Denied,
            $reader->authorize($account->id(), ModerationCapabilityV1::Audit, $this->now()),
        );
    }

    #[Test]
    public function audit_requires_its_dedicated_active_role_and_suspension_is_fail_closed(): void
    {
        $registry = new FakeAccountRegistry;
        $account = $this->account(2);
        $account->grantRole(RoleId::fromString('moderation_auditor'), $this->now()->modify('+1 minute'));
        $registry->add($account);
        $reader = new OwnerModeratorAuthorizationReaderV1($registry);

        self::assertSame(
            ModeratorAuthorizationDecisionV1::Allowed,
            $reader->authorize($account->id(), ModerationCapabilityV1::Audit, $this->now()),
        );
        $stored = $registry->find($account->id());
        self::assertNotNull($stored);
        $stored->suspend($this->now()->modify('+2 minutes'));
        $registry->save($stored, $stored->version() - 1);
        self::assertSame(
            ModeratorAuthorizationDecisionV1::Denied,
            $reader->authorize($account->id(), ModerationCapabilityV1::Audit, $this->now()),
        );
    }

    #[Test]
    public function missing_corrupted_and_unavailable_sources_are_fail_closed(): void
    {
        $missing = new OwnerModeratorAuthorizationReaderV1(new FakeAccountRegistry);
        self::assertSame(
            ModeratorAuthorizationDecisionV1::Denied,
            $missing->authorize($this->id(3), ModerationCapabilityV1::Report, $this->now()),
        );

        $corruptedRegistry = $this->createMock(AccountRegistry::class);
        $corruptedRegistry->method('find')->willReturn($this->account(4));
        $corrupted = new OwnerModeratorAuthorizationReaderV1($corruptedRegistry);
        self::assertSame(
            ModeratorAuthorizationDecisionV1::Corrupted,
            $corrupted->authorize($this->id(5), ModerationCapabilityV1::Report, $this->now()),
        );

        $unavailableRegistry = $this->createMock(AccountRegistry::class);
        $unavailableRegistry->method('find')->willThrowException(new RuntimeException('unavailable'));
        $unavailable = new OwnerModeratorAuthorizationReaderV1($unavailableRegistry);
        self::assertSame(
            ModeratorAuthorizationDecisionV1::DependencyUnavailable,
            $unavailable->authorize($this->id(6), ModerationCapabilityV1::Report, $this->now()),
        );
    }

    private function account(int $suffix): Account
    {
        return Account::register(
            $this->id($suffix),
            EmailAddress::fromString("moderator{$suffix}@appart.sn"),
            PhoneNumber::fromString('+2217712345'.sprintf('%02d', $suffix)),
            PersonName::fromString('Moderator Test'),
            PasswordHash::fromString('$generic$v=1$salt-initial$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $this->now()->modify('+1 hour'),
            $this->now(),
        );
    }

    private function id(int $suffix): AccountId
    {
        return AccountId::fromString(sprintf('53c20000-0000-4000-8000-%012d', $suffix));
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
