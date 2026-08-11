<?php

namespace Tests\Unit\IdentityAccess\PublicationReviewAuthorization;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\OwnerPublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewAuthorizationStatus;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewCapabilityV1;
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
use RuntimeException;
use Tests\Unit\Modules\IdentityAccess\Support\FakeAccountRegistry;

final class OwnerPublicationReviewAuthorizationReaderV1Test extends TestCase
{
    public function test_only_the_dedicated_role_allows_the_closed_capability_catalogue(): void
    {
        $registry = new FakeAccountRegistry;
        $reviewer = $this->account(1);
        $reviewer->grantRole(RoleId::fromString('publication_reviewer'), $this->now()->modify('+1 minute'));
        $registry->add($reviewer);
        $reader = new OwnerPublicationReviewAuthorizationReaderV1($registry);

        self::assertSame([
            'read_publication_review_queue',
            'claim_publication_review',
            'begin_publication_review',
            'approve_publication',
        ], array_map(static fn (PublicationReviewCapabilityV1 $capability): string => $capability->value, PublicationReviewCapabilityV1::cases()));

        foreach (PublicationReviewCapabilityV1::cases() as $capability) {
            self::assertSame(PublicationReviewAuthorizationStatus::Allowed, $reader->authorize($reviewer->id(), $capability, $this->now())->status);
        }
    }

    public function test_moderator_missing_role_and_suspended_reviewer_are_denied(): void
    {
        $registry = new FakeAccountRegistry;
        $moderator = $this->account(2);
        $moderator->grantRole(RoleId::fromString('moderator'), $this->now()->modify('+1 minute'));
        $registry->add($moderator);
        $reader = new OwnerPublicationReviewAuthorizationReaderV1($registry);

        self::assertSame(PublicationReviewAuthorizationStatus::Denied, $reader->authorize($moderator->id(), PublicationReviewCapabilityV1::ApprovePublication, $this->now())->status);
        self::assertSame(PublicationReviewAuthorizationStatus::Denied, $reader->authorize($this->id(3), PublicationReviewCapabilityV1::ReadPublicationReviewQueue, $this->now())->status);

        $reviewer = $this->account(4);
        $reviewer->grantRole(RoleId::fromString('publication_reviewer'), $this->now()->modify('+1 minute'));
        $reviewer->suspend($this->now()->modify('+2 minutes'));
        $registry->add($reviewer);
        self::assertSame(PublicationReviewAuthorizationStatus::Denied, $reader->authorize($reviewer->id(), PublicationReviewCapabilityV1::BeginPublicationReview, $this->now())->status);
    }

    public function test_corrupted_and_unavailable_sources_fail_closed(): void
    {
        $corrupted = $this->createMock(AccountRegistry::class);
        $corrupted->method('find')->willReturn($this->account(5));
        self::assertSame(
            PublicationReviewAuthorizationStatus::DependencyUnavailable,
            (new OwnerPublicationReviewAuthorizationReaderV1($corrupted))->authorize($this->id(6), PublicationReviewCapabilityV1::ClaimPublicationReview, $this->now())->status,
        );

        $unavailable = $this->createMock(AccountRegistry::class);
        $unavailable->method('find')->willThrowException(new RuntimeException('unavailable'));
        self::assertSame(
            PublicationReviewAuthorizationStatus::DependencyUnavailable,
            (new OwnerPublicationReviewAuthorizationReaderV1($unavailable))->authorize($this->id(7), PublicationReviewCapabilityV1::ClaimPublicationReview, $this->now())->status,
        );
    }

    private function account(int $suffix): Account
    {
        return Account::register(
            $this->id($suffix),
            EmailAddress::fromString("publication-reviewer{$suffix}@appart.sn"),
            PhoneNumber::fromString('+2217712345'.sprintf('%02d', $suffix)),
            PersonName::fromString('Publication Reviewer'),
            PasswordHash::fromString('$generic$v=1$salt-initial$'.str_repeat('x', 40)),
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $this->now()->modify('+1 hour'),
            $this->now(),
        );
    }

    private function id(int $suffix): AccountId
    {
        return AccountId::fromString(sprintf('63c20000-0000-4000-8000-%012d', $suffix));
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-11T12:00:00+00:00');
    }
}
