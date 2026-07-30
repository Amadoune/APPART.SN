<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Domain\Exception\ConcurrentAccountModification;
use Appart\Modules\IdentityAccess\Domain\Exception\DuplicateAccountIdentity;
use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\ConsentPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\CorruptedHistoricalAccountPersistence;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\CredentialPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\HistoricalAccountPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\RoleAssignmentPersistenceSnapshotV1;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\VerificationPersistenceSnapshotV1;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use Throwable;

final readonly class PostgreSqlAccountRepository implements AccountRegistry
{
    public function __construct(
        private PDO $connection,
        private AccountPersistenceMapper $mapper,
    ) {}

    public function find(AccountId $id): ?Account
    {
        $statement = $this->connection->prepare(
            'SELECT account_id::text,email,phone,person_name,last_changed_at,last_changed_at_offset,historical_suspended,historical_version,snapshot_version FROM identity_access.accounts WHERE account_id=CAST(:account_id AS uuid)',
        );
        $statement->execute(['account_id' => $id->value]);
        $root = $statement->fetch(PDO::FETCH_ASSOC);
        if ($root === false) {
            return null;
        }

        try {
            return $this->mapper->account($this->snapshot($root));
        } catch (Throwable $error) {
            throw CorruptedHistoricalAccountPersistence::detected($error);
        }
    }

    public function add(Account $account): void
    {
        $snapshot = $this->mapper->snapshot($account);
        if ($snapshot->historicalVersion !== 0) {
            throw CorruptedHistoricalAccountPersistence::detected();
        }

        try {
            $this->withinTransaction(function () use ($snapshot): void {
                $this->insertRoot($snapshot);
                $this->replaceChildren($snapshot);
            });
        } catch (PDOException $error) {
            $this->throwDuplicate($error);
            throw CorruptedHistoricalAccountPersistence::detected($error);
        }
    }

    public function save(Account $account, int $expectedVersion): void
    {
        $snapshot = $this->mapper->snapshot($account);
        if ($expectedVersion < 0 || $snapshot->historicalVersion !== $expectedVersion + 1) {
            throw new ConcurrentAccountModification;
        }

        try {
            $this->withinTransaction(function () use ($snapshot, $expectedVersion): void {
                $statement = $this->connection->prepare(
                    'UPDATE identity_access.accounts SET email=:email,phone=:phone,person_name=:person_name,last_changed_at=CAST(:last_changed_at AS timestamptz),last_changed_at_offset=:last_changed_at_offset,historical_suspended=:historical_suspended,historical_version=:historical_version,snapshot_version=:snapshot_version WHERE account_id=CAST(:account_id AS uuid) AND historical_version=:expected_version',
                );
                $statement->execute($this->rootParameters($snapshot) + ['expected_version' => $expectedVersion]);
                if ($statement->rowCount() !== 1) {
                    throw new ConcurrentAccountModification;
                }
                $this->replaceChildren($snapshot);
            });
        } catch (ConcurrentAccountModification $error) {
            throw $error;
        } catch (PDOException $error) {
            $this->throwDuplicate($error);
            throw CorruptedHistoricalAccountPersistence::detected($error);
        }
    }

    /** @param callable(): void $operation */
    private function withinTransaction(callable $operation): void
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }

        try {
            $operation();
            if ($owner) {
                $this->connection->commit();
            }
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }

    private function insertRoot(HistoricalAccountPersistenceSnapshotV1 $snapshot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.accounts(account_id,email,phone,person_name,last_changed_at,last_changed_at_offset,historical_suspended,historical_version,snapshot_version) VALUES(CAST(:account_id AS uuid),:email,:phone,:person_name,CAST(:last_changed_at AS timestamptz),:last_changed_at_offset,:historical_suspended,:historical_version,:snapshot_version)',
        );
        $statement->execute($this->rootParameters($snapshot));
    }

    /** @return array<string, int|string> */
    private function rootParameters(HistoricalAccountPersistenceSnapshotV1 $snapshot): array
    {
        return [
            'account_id' => $snapshot->accountId->value,
            'email' => $snapshot->email->value,
            'phone' => $snapshot->phone->value,
            'person_name' => $snapshot->name->value,
            'last_changed_at' => $snapshot->lastChangedAt->format('Y-m-d\TH:i:s.uP'),
            'last_changed_at_offset' => $this->offset($snapshot->lastChangedAt),
            'historical_suspended' => $snapshot->historicalSuspended ? 1 : 0,
            'historical_version' => $snapshot->historicalVersion,
            'snapshot_version' => $snapshot->snapshotVersion,
        ];
    }

    private function replaceChildren(HistoricalAccountPersistenceSnapshotV1 $snapshot): void
    {
        foreach (['account_credentials', 'account_verifications', 'account_role_assignments', 'account_consents'] as $table) {
            $statement = $this->connection->prepare("DELETE FROM identity_access.{$table} WHERE account_id=CAST(:account_id AS uuid)");
            $statement->execute(['account_id' => $snapshot->accountId->value]);
        }

        $this->insertCredential($snapshot);
        $this->insertVerification($snapshot->accountId, $snapshot->emailVerification);
        $this->insertVerification($snapshot->accountId, $snapshot->phoneVerification);
        foreach ($snapshot->roleAssignments as $assignment) {
            $this->insertRoleAssignment($snapshot->accountId, $assignment);
        }
        foreach ($snapshot->consents as $consent) {
            $this->insertConsent($snapshot->accountId, $consent);
        }
    }

    private function insertCredential(HistoricalAccountPersistenceSnapshotV1 $snapshot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.account_credentials(account_id,encoded_password_hash,changed_at,changed_at_offset) VALUES(CAST(:account_id AS uuid),:encoded_password_hash,CAST(:changed_at AS timestamptz),:changed_at_offset)',
        );
        $statement->execute([
            'account_id' => $snapshot->accountId->value,
            'encoded_password_hash' => $snapshot->credential->encodedPasswordHash->revealForPersistence(),
            'changed_at' => $snapshot->credential->changedAt->format('Y-m-d\TH:i:s.uP'),
            'changed_at_offset' => $this->offset($snapshot->credential->changedAt),
        ]);
    }

    private function insertVerification(AccountId $id, VerificationPersistenceSnapshotV1 $snapshot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.account_verifications(account_id,channel,verification_token,issued_at,issued_at_offset,expires_at,expires_at_offset,verified_at,verified_at_offset) VALUES(CAST(:account_id AS uuid),:channel,:verification_token,CAST(:issued_at AS timestamptz),:issued_at_offset,CAST(:expires_at AS timestamptz),:expires_at_offset,CAST(:verified_at AS timestamptz),:verified_at_offset)',
        );
        $statement->execute([
            'account_id' => $id->value,
            'channel' => $snapshot->channel->value,
            'verification_token' => $snapshot->verificationToken->revealForPersistence(),
            'issued_at' => $snapshot->issuedAt->format('Y-m-d\TH:i:s.uP'),
            'issued_at_offset' => $this->offset($snapshot->issuedAt),
            'expires_at' => $snapshot->expiresAt->format('Y-m-d\TH:i:s.uP'),
            'expires_at_offset' => $this->offset($snapshot->expiresAt),
            'verified_at' => $snapshot->verifiedAt?->format('Y-m-d\TH:i:s.uP'),
            'verified_at_offset' => $snapshot->verifiedAt === null ? null : $this->offset($snapshot->verifiedAt),
        ]);
    }

    private function insertRoleAssignment(AccountId $id, RoleAssignmentPersistenceSnapshotV1 $snapshot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.account_role_assignments(account_id,ordinal,role_id,granted_at,granted_at_offset,revoked_at,revoked_at_offset) VALUES(CAST(:account_id AS uuid),:ordinal,:role_id,CAST(:granted_at AS timestamptz),:granted_at_offset,CAST(:revoked_at AS timestamptz),:revoked_at_offset)',
        );
        $statement->execute([
            'account_id' => $id->value,
            'ordinal' => $snapshot->ordinal,
            'role_id' => $snapshot->roleId->value,
            'granted_at' => $snapshot->grantedAt->format('Y-m-d\TH:i:s.uP'),
            'granted_at_offset' => $this->offset($snapshot->grantedAt),
            'revoked_at' => $snapshot->revokedAt?->format('Y-m-d\TH:i:s.uP'),
            'revoked_at_offset' => $snapshot->revokedAt === null ? null : $this->offset($snapshot->revokedAt),
        ]);
    }

    private function insertConsent(AccountId $id, ConsentPersistenceSnapshotV1 $snapshot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO identity_access.account_consents(account_id,ordinal,purpose,granted_at,granted_at_offset,withdrawn_at,withdrawn_at_offset) VALUES(CAST(:account_id AS uuid),:ordinal,:purpose,CAST(:granted_at AS timestamptz),:granted_at_offset,CAST(:withdrawn_at AS timestamptz),:withdrawn_at_offset)',
        );
        $statement->execute([
            'account_id' => $id->value,
            'ordinal' => $snapshot->ordinal,
            'purpose' => $snapshot->purpose->value,
            'granted_at' => $snapshot->grantedAt->format('Y-m-d\TH:i:s.uP'),
            'granted_at_offset' => $this->offset($snapshot->grantedAt),
            'withdrawn_at' => $snapshot->withdrawnAt?->format('Y-m-d\TH:i:s.uP'),
            'withdrawn_at_offset' => $snapshot->withdrawnAt === null ? null : $this->offset($snapshot->withdrawnAt),
        ]);
    }

    /** @param array<string, mixed> $root */
    private function snapshot(array $root): HistoricalAccountPersistenceSnapshotV1
    {
        if ((int) $root['snapshot_version'] !== HistoricalAccountPersistenceSnapshotV1::VERSION) {
            throw CorruptedHistoricalAccountPersistence::detected();
        }

        $id = AccountId::fromString((string) $root['account_id']);
        $credential = $this->credential($id);
        $verifications = $this->verifications($id);

        return new HistoricalAccountPersistenceSnapshotV1(
            $id,
            EmailAddress::fromString((string) $root['email']),
            PhoneNumber::fromString((string) $root['phone']),
            PersonName::fromString((string) $root['person_name']),
            $this->date((string) $root['last_changed_at'], (int) $root['last_changed_at_offset']),
            (bool) $root['historical_suspended'],
            (int) $root['historical_version'],
            $credential,
            $verifications[VerificationChannel::Email->value] ?? throw CorruptedHistoricalAccountPersistence::detected(),
            $verifications[VerificationChannel::Phone->value] ?? throw CorruptedHistoricalAccountPersistence::detected(),
            $this->roleAssignments($id),
            $this->consents($id),
        );
    }

    private function credential(AccountId $id): CredentialPersistenceSnapshotV1
    {
        $statement = $this->connection->prepare(
            'SELECT encoded_password_hash,changed_at,changed_at_offset FROM identity_access.account_credentials WHERE account_id=CAST(:account_id AS uuid)',
        );
        $statement->execute(['account_id' => $id->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw CorruptedHistoricalAccountPersistence::detected();
        }

        return new CredentialPersistenceSnapshotV1(
            SensitivePersistenceValueV1::fromSecret((string) $row['encoded_password_hash']),
            $this->date((string) $row['changed_at'], (int) $row['changed_at_offset']),
        );
    }

    /** @return array<string, VerificationPersistenceSnapshotV1> */
    private function verifications(AccountId $id): array
    {
        $statement = $this->connection->prepare(
            'SELECT channel,verification_token,issued_at,issued_at_offset,expires_at,expires_at_offset,verified_at,verified_at_offset FROM identity_access.account_verifications WHERE account_id=CAST(:account_id AS uuid) ORDER BY channel',
        );
        $statement->execute(['account_id' => $id->value]);
        $result = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $channel = VerificationChannel::from((string) $row['channel']);
            $result[$channel->value] = new VerificationPersistenceSnapshotV1(
                $channel,
                SensitivePersistenceValueV1::fromSecret((string) $row['verification_token']),
                $this->date((string) $row['issued_at'], (int) $row['issued_at_offset']),
                $this->date((string) $row['expires_at'], (int) $row['expires_at_offset']),
                $row['verified_at'] === null ? null : $this->date((string) $row['verified_at'], (int) $row['verified_at_offset']),
            );
        }
        if (count($result) !== 2) {
            throw CorruptedHistoricalAccountPersistence::detected();
        }

        return $result;
    }

    /** @return list<RoleAssignmentPersistenceSnapshotV1> */
    private function roleAssignments(AccountId $id): array
    {
        $statement = $this->connection->prepare(
            'SELECT ordinal,role_id,granted_at,granted_at_offset,revoked_at,revoked_at_offset FROM identity_access.account_role_assignments WHERE account_id=CAST(:account_id AS uuid) ORDER BY ordinal',
        );
        $statement->execute(['account_id' => $id->value]);

        return array_map(fn (array $row): RoleAssignmentPersistenceSnapshotV1 => new RoleAssignmentPersistenceSnapshotV1(
            RoleId::fromString((string) $row['role_id']),
            $this->date((string) $row['granted_at'], (int) $row['granted_at_offset']),
            $row['revoked_at'] === null ? null : $this->date((string) $row['revoked_at'], (int) $row['revoked_at_offset']),
            (int) $row['ordinal'],
        ), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return list<ConsentPersistenceSnapshotV1> */
    private function consents(AccountId $id): array
    {
        $statement = $this->connection->prepare(
            'SELECT ordinal,purpose,granted_at,granted_at_offset,withdrawn_at,withdrawn_at_offset FROM identity_access.account_consents WHERE account_id=CAST(:account_id AS uuid) ORDER BY ordinal',
        );
        $statement->execute(['account_id' => $id->value]);

        return array_map(fn (array $row): ConsentPersistenceSnapshotV1 => new ConsentPersistenceSnapshotV1(
            ConsentPurpose::fromString((string) $row['purpose']),
            $this->date((string) $row['granted_at'], (int) $row['granted_at_offset']),
            $row['withdrawn_at'] === null ? null : $this->date((string) $row['withdrawn_at'], (int) $row['withdrawn_at_offset']),
            (int) $row['ordinal'],
        ), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    private function date(string $value, int $offset): DateTimeImmutable
    {
        $sign = $offset < 0 ? '-' : '+';
        $absolute = abs($offset);
        $zone = new DateTimeZone(sprintf('%s%02d:%02d', $sign, intdiv($absolute, 60), $absolute % 60));

        return (new DateTimeImmutable($value))->setTimezone($zone);
    }

    private function offset(DateTimeImmutable $value): int
    {
        return intdiv($value->getOffset(), 60);
    }

    private function throwDuplicate(PDOException $error): void
    {
        if ($error->getCode() !== '23505') {
            return;
        }
        $message = $error->getMessage();
        if (str_contains($message, 'historical_accounts_email_uq')) {
            throw DuplicateAccountIdentity::email();
        }
        if (str_contains($message, 'historical_accounts_phone_uq')) {
            throw DuplicateAccountIdentity::phone();
        }
        if (str_contains($message, 'accounts_pkey')) {
            throw DuplicateAccountIdentity::accountId();
        }
    }
}
