<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Model\Account;
use Appart\Modules\IdentityAccess\Domain\Model\Consent;
use Appart\Modules\IdentityAccess\Domain\Model\Credential;
use Appart\Modules\IdentityAccess\Domain\Model\RoleAssignment;
use Appart\Modules\IdentityAccess\Domain\Model\Verification;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;

final readonly class AccountPersistenceMapper
{
    public function snapshot(Account $account): HistoricalAccountPersistenceSnapshotV1
    {
        $state = $account->persistenceState();

        return new HistoricalAccountPersistenceSnapshotV1(
            $state['accountId'],
            $state['email'],
            $state['phone'],
            $state['name'],
            $state['lastChangedAt'],
            $state['historicalSuspended'],
            $state['historicalVersion'],
            new CredentialPersistenceSnapshotV1(
                $state['credential']['encodedPasswordHash'],
                $state['credential']['changedAt'],
            ),
            $this->verificationSnapshot($state['emailVerification']),
            $this->verificationSnapshot($state['phoneVerification']),
            array_map(
                static fn (array $item): RoleAssignmentPersistenceSnapshotV1 => new RoleAssignmentPersistenceSnapshotV1(
                    $item['roleId'], $item['grantedAt'], $item['revokedAt'], $item['ordinal'],
                ),
                $state['roleAssignments'],
            ),
            array_map(
                static fn (array $item): ConsentPersistenceSnapshotV1 => new ConsentPersistenceSnapshotV1(
                    $item['purpose'], $item['grantedAt'], $item['withdrawnAt'], $item['ordinal'],
                ),
                $state['consents'],
            ),
        );
    }

    public function account(HistoricalAccountPersistenceSnapshotV1 $snapshot): Account
    {
        $emailVerification = $this->verification($snapshot->emailVerification);
        $phoneVerification = $this->verification($snapshot->phoneVerification);

        return Account::reconstitute(
            $snapshot->accountId,
            $snapshot->email,
            $snapshot->phone,
            $snapshot->name,
            new Credential(
                PasswordHash::fromString($snapshot->credential->encodedPasswordHash->revealForPersistence()),
                $snapshot->credential->changedAt,
            ),
            $snapshot->lastChangedAt,
            [
                $emailVerification->channel->value => $emailVerification,
                $phoneVerification->channel->value => $phoneVerification,
            ],
            array_map($this->roleAssignment(...), $snapshot->roleAssignments),
            array_map($this->consent(...), $snapshot->consents),
            $snapshot->historicalSuspended,
            $snapshot->historicalVersion,
        );
    }

    private function verification(VerificationPersistenceSnapshotV1 $snapshot): Verification
    {
        return Verification::reconstitute(
            $snapshot->channel,
            VerificationToken::forChannel(
                $snapshot->channel,
                $snapshot->verificationToken->revealForPersistence(),
            ),
            $snapshot->issuedAt,
            $snapshot->expiresAt,
            $snapshot->verifiedAt,
        );
    }

    /**
     * @param  array{channel: VerificationChannel, verificationToken: SensitivePersistenceValueV1, issuedAt: \DateTimeImmutable, expiresAt: \DateTimeImmutable, verifiedAt: ?\DateTimeImmutable}  $state
     */
    private function verificationSnapshot(array $state): VerificationPersistenceSnapshotV1
    {
        return new VerificationPersistenceSnapshotV1(
            $state['channel'],
            $state['verificationToken'],
            $state['issuedAt'],
            $state['expiresAt'],
            $state['verifiedAt'],
        );
    }

    private function roleAssignment(RoleAssignmentPersistenceSnapshotV1 $snapshot): RoleAssignment
    {
        return RoleAssignment::reconstitute(
            $snapshot->roleId,
            $snapshot->grantedAt,
            $snapshot->revokedAt,
        );
    }

    private function consent(ConsentPersistenceSnapshotV1 $snapshot): Consent
    {
        return Consent::reconstitute(
            $snapshot->purpose,
            $snapshot->grantedAt,
            $snapshot->withdrawnAt,
        );
    }
}
