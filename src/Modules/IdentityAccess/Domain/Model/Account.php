<?php

namespace Appart\Modules\IdentityAccess\Domain\Model;

use Appart\Modules\IdentityAccess\Domain\Event\AbstractAccountEvent;
use Appart\Modules\IdentityAccess\Domain\Event\AccountEvent;
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
use Appart\Modules\IdentityAccess\Domain\Event\VerificationReplaced;
use Appart\Modules\IdentityAccess\Domain\Exception\AccountStateViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\ConsentViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\RoleAssignmentViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\TemporalConsistencyViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\VerificationFailed;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
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

final class Account
{
    /** @var array<string, Verification> */
    private array $verifications;

    /** @var list<RoleAssignment> */
    private array $roleAssignments = [];

    /** @var list<Consent> */
    private array $consents = [];

    /** @var list<AccountEvent> */
    private array $recordedEvents = [];

    private bool $suspended = false;

    private int $version = 0;

    private function __construct(
        private readonly AccountId $id,
        private readonly EmailAddress $email,
        private readonly PhoneNumber $phone,
        private readonly PersonName $name,
        private Credential $credential,
        private DateTimeImmutable $lastChangedAt,
        Verification $emailVerification,
        Verification $phoneVerification,
    ) {
        $this->verifications = [
            VerificationChannel::Email->value => $emailVerification,
            VerificationChannel::Phone->value => $phoneVerification,
        ];
    }

    public static function register(
        AccountId $id,
        EmailAddress $email,
        PhoneNumber $phone,
        PersonName $name,
        PasswordHash $passwordHash,
        VerificationToken $emailToken,
        VerificationToken $phoneToken,
        DateTimeImmutable $verificationExpiresAt,
        DateTimeImmutable $registeredAt,
    ): self {
        if ($verificationExpiresAt <= $registeredAt) {
            throw VerificationFailed::expiredToken();
        }

        $account = new self(
            $id,
            $email,
            $phone,
            $name,
            new Credential($passwordHash, $registeredAt),
            $registeredAt,
            new Verification(VerificationChannel::Email, $emailToken, $registeredAt, $verificationExpiresAt),
            new Verification(VerificationChannel::Phone, $phoneToken, $registeredAt, $verificationExpiresAt),
        );
        $account->record(new AccountRegistered($id, $registeredAt), 0);

        return $account;
    }

    public function verifyEmail(VerificationToken $token, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $this->guardActive();
        $this->verifications[VerificationChannel::Email->value]->verify($token, $at);
        $this->record(new EmailVerified($this->id, $at));
        $this->changedAt($at);
    }

    /**
     * @param  array<string, Verification>  $verifications
     * @param  list<RoleAssignment>  $roleAssignments
     * @param  list<Consent>  $consents
     */
    public static function reconstitute(AccountId $id, EmailAddress $email, PhoneNumber $phone, PersonName $name, Credential $credential, DateTimeImmutable $lastChangedAt, array $verifications, array $roleAssignments, array $consents, bool $suspended, int $version): self
    {
        $emailVerification = $verifications[VerificationChannel::Email->value] ?? throw AccountStateViolation::invalidReconstitution();
        $phoneVerification = $verifications[VerificationChannel::Phone->value] ?? throw AccountStateViolation::invalidReconstitution();
        if ($version < 0) {
            throw AccountStateViolation::invalidReconstitution();
        }
        $account = new self($id, $email, $phone, $name, $credential, $lastChangedAt, $emailVerification, $phoneVerification);
        $account->roleAssignments = $roleAssignments;
        $account->consents = $consents;
        $account->suspended = $suspended;
        $account->version = $version;

        return $account;
    }

    public function verifyPhone(VerificationToken $token, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $this->guardActive();
        $this->verifications[VerificationChannel::Phone->value]->verify($token, $at);
        $this->record(new PhoneVerified($this->id, $at));
        $this->changedAt($at);
    }

    public function changePassword(PasswordHash $newHash, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $this->guardActive();
        $this->credential->change($newHash, $at);
        $this->record(new PasswordChanged($this->id, $at));
        $this->changedAt($at);
    }

    public function suspend(DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        if ($this->suspended) {
            throw AccountStateViolation::alreadySuspended();
        }
        foreach ($this->roleAssignments as $assignment) {
            if ($assignment->isActive()) {
                $assignment->revoke($at);
                $this->record(new RoleRevoked($this->id, $assignment->roleId, $at));
            }
        }
        $this->suspended = true;
        $this->record(new AccountSuspended($this->id, $at));
        $this->changedAt($at);
    }

    public function reactivate(DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        if (! $this->suspended) {
            throw AccountStateViolation::alreadyActive();
        }
        $this->suspended = false;
        $this->record(new AccountReactivated($this->id, $at));
        $this->changedAt($at);
    }

    public function grantRole(RoleId $roleId, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $this->guardActive();
        if ($this->activeRoleAssignment($roleId) !== null) {
            throw RoleAssignmentViolation::alreadyGranted();
        }
        $this->roleAssignments[] = new RoleAssignment($roleId, $at);
        $this->record(new RoleGranted($this->id, $roleId, $at));
        $this->changedAt($at);
    }

    public function revokeRole(RoleId $roleId, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $assignment = $this->activeRoleAssignment($roleId) ?? throw RoleAssignmentViolation::notGranted();
        $assignment->revoke($at);
        $this->record(new RoleRevoked($this->id, $roleId, $at));
        $this->changedAt($at);
    }

    public function grantConsent(ConsentPurpose $purpose, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        if ($this->activeConsent($purpose) !== null) {
            throw ConsentViolation::alreadyGranted();
        }
        $this->consents[] = new Consent($purpose, $at);
        $this->record(new ConsentGranted($this->id, $purpose, $at));
        $this->changedAt($at);
    }

    public function withdrawConsent(ConsentPurpose $purpose, DateTimeImmutable $at): void
    {
        $this->guardChangeAt($at);
        $consent = $this->activeConsent($purpose) ?? throw ConsentViolation::notGranted();
        $consent->withdraw($at);
        $this->record(new ConsentWithdrawn($this->id, $purpose, $at));
        $this->changedAt($at);
    }

    public function replaceEmailVerification(VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        $this->replaceVerification(VerificationChannel::Email, $token, $issuedAt, $expiresAt);
    }

    public function replacePhoneVerification(VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        $this->replaceVerification(VerificationChannel::Phone, $token, $issuedAt, $expiresAt);
    }

    public function id(): AccountId
    {
        return $this->id;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function phone(): PhoneNumber
    {
        return $this->phone;
    }

    public function name(): PersonName
    {
        return $this->name;
    }

    public function passwordMatches(PasswordHash $candidate): bool
    {
        return $this->credential->matches($candidate);
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function version(): int
    {
        return $this->version;
    }

    /**
     * @return array{
     *   accountId: AccountId,
     *   email: EmailAddress,
     *   phone: PhoneNumber,
     *   name: PersonName,
     *   lastChangedAt: DateTimeImmutable,
     *   historicalSuspended: bool,
     *   historicalVersion: int,
     *   credential: array{encodedPasswordHash: SensitivePersistenceValueV1, changedAt: DateTimeImmutable},
     *   emailVerification: array{channel: VerificationChannel, verificationToken: SensitivePersistenceValueV1, issuedAt: DateTimeImmutable, expiresAt: DateTimeImmutable, verifiedAt: ?DateTimeImmutable},
     *   phoneVerification: array{channel: VerificationChannel, verificationToken: SensitivePersistenceValueV1, issuedAt: DateTimeImmutable, expiresAt: DateTimeImmutable, verifiedAt: ?DateTimeImmutable},
     *   roleAssignments: list<array{roleId: RoleId, grantedAt: DateTimeImmutable, revokedAt: ?DateTimeImmutable, ordinal: int}>,
     *   consents: list<array{purpose: ConsentPurpose, grantedAt: DateTimeImmutable, withdrawnAt: ?DateTimeImmutable, ordinal: int}>
     * }
     */
    public function persistenceState(): array
    {
        return [
            'accountId' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'name' => $this->name,
            'lastChangedAt' => $this->lastChangedAt,
            'historicalSuspended' => $this->suspended,
            'historicalVersion' => $this->version,
            'credential' => $this->credential->persistenceState(),
            'emailVerification' => $this->verifications[VerificationChannel::Email->value]->persistenceState(),
            'phoneVerification' => $this->verifications[VerificationChannel::Phone->value]->persistenceState(),
            'roleAssignments' => array_map(
                static fn (RoleAssignment $assignment, int $ordinal): array => $assignment->persistenceState($ordinal),
                $this->roleAssignments,
                array_keys($this->roleAssignments),
            ),
            'consents' => array_map(
                static fn (Consent $consent, int $ordinal): array => $consent->persistenceState($ordinal),
                $this->consents,
                array_keys($this->consents),
            ),
        ];
    }

    public function isEmailVerified(): bool
    {
        return $this->verifications[VerificationChannel::Email->value]->isVerified();
    }

    public function isPhoneVerified(): bool
    {
        return $this->verifications[VerificationChannel::Phone->value]->isVerified();
    }

    public function hasRole(RoleId $roleId): bool
    {
        return ! $this->suspended && $this->activeRoleAssignment($roleId) !== null;
    }

    public function hasConsent(ConsentPurpose $purpose): bool
    {
        return $this->activeConsent($purpose) !== null;
    }

    /** @return list<AccountEvent> */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function activeRoleAssignment(RoleId $roleId): ?RoleAssignment
    {
        foreach (array_reverse($this->roleAssignments) as $assignment) {
            if ($assignment->roleId->equals($roleId) && $assignment->isActive()) {
                return $assignment;
            }
        }

        return null;
    }

    private function activeConsent(ConsentPurpose $purpose): ?Consent
    {
        foreach (array_reverse($this->consents) as $consent) {
            if ($consent->purpose->value === $purpose->value && $consent->isGranted()) {
                return $consent;
            }
        }

        return null;
    }

    private function guardActive(): void
    {
        if ($this->suspended) {
            throw AccountStateViolation::suspended();
        }
    }

    private function replaceVerification(VerificationChannel $channel, VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        $this->guardChangeAt($issuedAt);
        $this->guardActive();
        $this->verifications[$channel->value]->replace($token, $issuedAt, $expiresAt);
        $this->record(new VerificationReplaced($this->id, $channel, $issuedAt));
        $this->changedAt($issuedAt);
    }

    private function guardChangeAt(DateTimeImmutable $at): void
    {
        if ($at < $this->lastChangedAt) {
            throw TemporalConsistencyViolation::nonIncreasingTime();
        }
    }

    private function changedAt(DateTimeImmutable $at): void
    {
        $this->lastChangedAt = $at;
        $this->version++;
    }

    public function __clone()
    {
        $this->credential = clone $this->credential;
        foreach ($this->verifications as $key => $verification) {
            $this->verifications[$key] = clone $verification;
        }
        foreach ($this->roleAssignments as $key => $assignment) {
            $this->roleAssignments[$key] = clone $assignment;
        }
        foreach ($this->consents as $key => $consent) {
            $this->consents[$key] = clone $consent;
        }
    }

    private function record(AccountEvent $event, ?int $resultVersion = null): void
    {
        if ($event instanceof AbstractAccountEvent) {
            $event->stamp($resultVersion ?? $this->version + 1, count($this->recordedEvents) + 1);
        }
        $this->recordedEvents[] = $event;
    }
}
