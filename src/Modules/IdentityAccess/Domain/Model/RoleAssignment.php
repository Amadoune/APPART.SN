<?php

namespace Appart\Modules\IdentityAccess\Domain\Model;

use Appart\Modules\IdentityAccess\Domain\Exception\TemporalConsistencyViolation;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;

final class RoleAssignment
{
    private ?DateTimeImmutable $revokedAt = null;

    public function __construct(public readonly RoleId $roleId, public readonly DateTimeImmutable $grantedAt) {}

    public function revoke(DateTimeImmutable $revokedAt): void
    {
        if ($this->revokedAt !== null || $revokedAt < $this->grantedAt) {
            throw TemporalConsistencyViolation::nonIncreasingTime();
        }
        $this->revokedAt = $revokedAt;
    }

    public function isActive(): bool
    {
        return $this->revokedAt === null;
    }

    public function revokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public static function reconstitute(RoleId $roleId, DateTimeImmutable $grantedAt, ?DateTimeImmutable $revokedAt): self
    {
        $assignment = new self($roleId, $grantedAt);
        if ($revokedAt !== null) {
            if ($revokedAt < $grantedAt) {
                throw TemporalConsistencyViolation::nonIncreasingTime();
            }
            $assignment->revokedAt = $revokedAt;
        }

        return $assignment;
    }

    /** @return array{roleId: RoleId, grantedAt: DateTimeImmutable, revokedAt: ?DateTimeImmutable, ordinal: int} */
    public function persistenceState(int $ordinal): array
    {
        return ['roleId' => $this->roleId, 'grantedAt' => $this->grantedAt, 'revokedAt' => $this->revokedAt, 'ordinal' => $ordinal];
    }
}
