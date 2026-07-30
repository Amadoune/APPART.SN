<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidHistoricalAccountPersistenceState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\IdentityAccess\Domain\ValueObject\EmailAddress;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PersonName;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PhoneNumber;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use DateTimeImmutable;
use LogicException;

final readonly class HistoricalAccountPersistenceSnapshotV1
{
    public const int VERSION = 1;

    public int $snapshotVersion;

    /**
     * @param  list<RoleAssignmentPersistenceSnapshotV1>  $roleAssignments
     * @param  list<ConsentPersistenceSnapshotV1>  $consents
     */
    public function __construct(
        public AccountId $accountId,
        public EmailAddress $email,
        public PhoneNumber $phone,
        public PersonName $name,
        public DateTimeImmutable $lastChangedAt,
        public bool $historicalSuspended,
        public int $historicalVersion,
        public CredentialPersistenceSnapshotV1 $credential,
        public VerificationPersistenceSnapshotV1 $emailVerification,
        public VerificationPersistenceSnapshotV1 $phoneVerification,
        public array $roleAssignments,
        public array $consents,
    ) {
        $this->snapshotVersion = self::VERSION;
        $this->guard();
    }

    public function __serialize(): array
    {
        throw new LogicException('Historical Account persistence snapshots cannot be serialized.');
    }

    public function __debugInfo(): array
    {
        return [
            'snapshotVersion' => $this->snapshotVersion,
            'accountId' => $this->accountId,
            'email' => '[REDACTED]',
            'phone' => '[REDACTED]',
            'name' => '[REDACTED]',
            'lastChangedAt' => $this->lastChangedAt,
            'historicalSuspended' => $this->historicalSuspended,
            'historicalVersion' => $this->historicalVersion,
            'credential' => '[REDACTED]',
            'emailVerification' => '[REDACTED]',
            'phoneVerification' => '[REDACTED]',
            'roleAssignments' => count($this->roleAssignments),
            'consents' => count($this->consents),
        ];
    }

    private function guard(): void
    {
        if ($this->historicalVersion < 0) {
            throw InvalidHistoricalAccountPersistenceState::field('historical_version');
        }
        if ($this->emailVerification->channel !== VerificationChannel::Email
            || $this->phoneVerification->channel !== VerificationChannel::Phone) {
            throw InvalidHistoricalAccountPersistenceState::field('verifications');
        }
        if ($this->credential->changedAt > $this->lastChangedAt) {
            throw InvalidHistoricalAccountPersistenceState::field('credential.changed_at');
        }

        $this->guardRoleAssignments();
        $this->guardConsents();
    }

    private function guardRoleAssignments(): void
    {
        $active = [];
        foreach ($this->roleAssignments as $ordinal => $assignment) {
            if ($assignment->ordinal !== $ordinal) {
                throw InvalidHistoricalAccountPersistenceState::field('role_assignments.order');
            }
            if ($assignment->grantedAt > $this->lastChangedAt
                || ($assignment->revokedAt !== null && $assignment->revokedAt > $this->lastChangedAt)) {
                throw InvalidHistoricalAccountPersistenceState::field('role_assignments.chronology');
            }
            if ($assignment->revokedAt === null) {
                if (isset($active[$assignment->roleId->value])) {
                    throw InvalidHistoricalAccountPersistenceState::field('role_assignments.active_uniqueness');
                }
                $active[$assignment->roleId->value] = true;
            }
        }
    }

    private function guardConsents(): void
    {
        $active = [];
        foreach ($this->consents as $ordinal => $consent) {
            if ($consent->ordinal !== $ordinal) {
                throw InvalidHistoricalAccountPersistenceState::field('consents.order');
            }
            if ($consent->grantedAt > $this->lastChangedAt
                || ($consent->withdrawnAt !== null && $consent->withdrawnAt > $this->lastChangedAt)) {
                throw InvalidHistoricalAccountPersistenceState::field('consents.chronology');
            }
            if ($consent->withdrawnAt === null) {
                if (isset($active[$consent->purpose->value])) {
                    throw InvalidHistoricalAccountPersistenceState::field('consents.active_uniqueness');
                }
                $active[$consent->purpose->value] = true;
            }
        }
    }
}
