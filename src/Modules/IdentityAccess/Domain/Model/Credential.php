<?php

namespace Appart\Modules\IdentityAccess\Domain\Model;

use Appart\Modules\IdentityAccess\Domain\Exception\PasswordUnchanged;
use Appart\Modules\IdentityAccess\Domain\Exception\TemporalConsistencyViolation;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\PasswordHash;
use DateTimeImmutable;

final class Credential
{
    public function __construct(private PasswordHash $passwordHash, private DateTimeImmutable $changedAt) {}

    public function change(PasswordHash $newHash, DateTimeImmutable $changedAt): void
    {
        if ($changedAt < $this->changedAt) {
            throw TemporalConsistencyViolation::nonIncreasingTime();
        }
        if ($this->passwordHash->equals($newHash)) {
            throw new PasswordUnchanged;
        }
        $this->passwordHash = $newHash;
        $this->changedAt = $changedAt;
    }

    public function matches(PasswordHash $candidate): bool
    {
        return $this->passwordHash->equals($candidate);
    }

    public function changedAt(): DateTimeImmutable
    {
        return $this->changedAt;
    }

    /** @return array{encodedPasswordHash: SensitivePersistenceValueV1, changedAt: DateTimeImmutable} */
    public function persistenceState(): array
    {
        return ['encodedPasswordHash' => $this->passwordHash->persistenceValue(), 'changedAt' => $this->changedAt];
    }
}
