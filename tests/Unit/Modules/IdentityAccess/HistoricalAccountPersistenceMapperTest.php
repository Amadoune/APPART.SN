<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidHistoricalAccountPersistenceState;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\ConsentPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\HistoricalAccountPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\RoleAssignmentPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\VerificationPersistenceSnapshotV1;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class HistoricalAccountPersistenceMapperTest extends TestCase
{
    public function test_complete_snapshot_round_trip_preserves_every_persistable_value_and_no_events(): void
    {
        $account = AccountTestData::account();
        $base = new DateTimeImmutable('2026-07-26T09:00:00+00:00');
        $account->verifyEmail(
            VerificationToken::forChannel(VerificationChannel::Email, str_repeat('e', 40)),
            $base->modify('+10 minutes'),
        );
        $account->verifyPhone(
            VerificationToken::forChannel(VerificationChannel::Phone, str_repeat('p', 40)),
            $base->modify('+11 minutes'),
        );
        $account->grantRole(RoleId::fromString('manager'), $base->modify('+12 minutes'));
        $account->revokeRole(RoleId::fromString('manager'), $base->modify('+13 minutes'));
        $account->grantRole(RoleId::fromString('manager'), $base->modify('+14 minutes'));
        $account->grantConsent(ConsentPurpose::fromString('marketing'), $base->modify('+15 minutes'));
        $account->withdrawConsent(ConsentPurpose::fromString('marketing'), $base->modify('+16 minutes'));
        $account->grantConsent(ConsentPurpose::fromString('marketing'), $base->modify('+17 minutes'));
        $newHash = PasswordHash::fromString('$generic$v=1$new-salt$'.str_repeat('n', 40));
        $account->changePassword($newHash, $base->modify('+18 minutes'));
        $account->suspend($base->modify('+19 minutes'));
        $account->releaseEvents();

        $mapper = new AccountPersistenceMapper;
        $before = $mapper->snapshot($account);
        $restored = $mapper->account($before);
        $after = $mapper->snapshot($restored);

        self::assertSame(HistoricalAccountPersistenceSnapshotV1::VERSION, $before->snapshotVersion);
        self::assertSame($before->accountId->value, $after->accountId->value);
        self::assertSame($before->email->value, $after->email->value);
        self::assertSame($before->phone->value, $after->phone->value);
        self::assertSame($before->name->value, $after->name->value);
        self::assertSame($before->lastChangedAt->format('c'), $after->lastChangedAt->format('c'));
        self::assertSame($before->historicalSuspended, $after->historicalSuspended);
        self::assertSame($before->historicalVersion, $after->historicalVersion);
        self::assertSame(
            $before->credential->encodedPasswordHash->revealForPersistence(),
            $after->credential->encodedPasswordHash->revealForPersistence(),
        );
        self::assertSame(
            $before->credential->changedAt->format('c'),
            $after->credential->changedAt->format('c'),
        );
        self::assertSame(2, count([$after->emailVerification, $after->phoneVerification]));
        self::assertSame(2, count($after->roleAssignments));
        self::assertSame(2, count($after->consents));
        self::assertSame([0, 1], array_column($after->roleAssignments, 'ordinal'));
        self::assertSame([0, 1], array_column($after->consents, 'ordinal'));
        self::assertSame([], $restored->releaseEvents());
        self::assertTrue($restored->passwordMatches($newHash));
        self::assertTrue($restored->isEmailVerified());
        self::assertTrue($restored->isPhoneVerified());
        self::assertTrue($restored->isSuspended());
        self::assertSame($account->version(), $restored->version());
    }

    public function test_negative_version_is_rejected(): void
    {
        $snapshot = (new AccountPersistenceMapper)->snapshot(AccountTestData::account());

        $this->expectException(InvalidHistoricalAccountPersistenceState::class);
        $this->copy($snapshot, historicalVersion: -1);
    }

    public function test_missing_or_duplicated_verification_channel_is_rejected(): void
    {
        $snapshot = (new AccountPersistenceMapper)->snapshot(AccountTestData::account());

        $this->expectException(InvalidHistoricalAccountPersistenceState::class);
        $this->copy($snapshot, phoneVerification: $snapshot->emailVerification);
    }

    public function test_invalid_verification_chronology_is_rejected(): void
    {
        $at = new DateTimeImmutable('2026-07-26T09:00:00+00:00');

        $this->expectException(InvalidHistoricalAccountPersistenceState::class);
        new VerificationPersistenceSnapshotV1(
            VerificationChannel::Email,
            SensitivePersistenceValueV1::fromSecret(str_repeat('e', 40)),
            $at,
            $at,
            null,
        );
    }

    public function test_duplicate_active_role_is_rejected(): void
    {
        $snapshot = (new AccountPersistenceMapper)->snapshot(AccountTestData::account());
        $at = $snapshot->lastChangedAt;
        $roles = [
            new RoleAssignmentPersistenceSnapshotV1(RoleId::fromString('manager'), $at, null, 0),
            new RoleAssignmentPersistenceSnapshotV1(RoleId::fromString('manager'), $at, null, 1),
        ];

        $this->expectException(InvalidHistoricalAccountPersistenceState::class);
        $this->copy($snapshot, roleAssignments: $roles);
    }

    public function test_duplicate_active_consent_is_rejected(): void
    {
        $snapshot = (new AccountPersistenceMapper)->snapshot(AccountTestData::account());
        $at = $snapshot->lastChangedAt;
        $consents = [
            new ConsentPersistenceSnapshotV1(ConsentPurpose::fromString('marketing'), $at, null, 0),
            new ConsentPersistenceSnapshotV1(ConsentPurpose::fromString('marketing'), $at, null, 1),
        ];

        $this->expectException(InvalidHistoricalAccountPersistenceState::class);
        $this->copy($snapshot, consents: $consents);
    }

    public function test_sensitive_values_cannot_be_stringified_or_debugged_in_clear(): void
    {
        $secretText = 'never-log-this-secret-value';
        $secret = SensitivePersistenceValueV1::fromSecret($secretText);

        self::assertStringNotContainsString($secretText, print_r($secret, true));
    }

    public function test_sensitive_values_cannot_be_php_serialized(): void
    {
        $secret = SensitivePersistenceValueV1::fromSecret('never-serialize-this-secret');

        $this->expectException(LogicException::class);
        serialize($secret);
    }

    public function test_sensitive_values_cannot_be_json_serialized(): void
    {
        $secret = SensitivePersistenceValueV1::fromSecret('never-json-this-secret');

        $this->expectException(LogicException::class);
        json_encode($secret, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  list<RoleAssignmentPersistenceSnapshotV1>|null  $roleAssignments
     * @param  list<ConsentPersistenceSnapshotV1>|null  $consents
     */
    private function copy(
        HistoricalAccountPersistenceSnapshotV1 $snapshot,
        ?int $historicalVersion = null,
        ?VerificationPersistenceSnapshotV1 $phoneVerification = null,
        ?array $roleAssignments = null,
        ?array $consents = null,
    ): HistoricalAccountPersistenceSnapshotV1 {
        return new HistoricalAccountPersistenceSnapshotV1(
            $snapshot->accountId,
            $snapshot->email,
            $snapshot->phone,
            $snapshot->name,
            $snapshot->lastChangedAt,
            $snapshot->historicalSuspended,
            $historicalVersion ?? $snapshot->historicalVersion,
            $snapshot->credential,
            $snapshot->emailVerification,
            $phoneVerification ?? $snapshot->phoneVerification,
            $roleAssignments ?? $snapshot->roleAssignments,
            $consents ?? $snapshot->consents,
        );
    }
}
