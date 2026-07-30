<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidHistoricalAccountPersistenceState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\RoleId;
use DateTimeImmutable;

final readonly class RoleAssignmentPersistenceSnapshotV1
{
    public function __construct(
        public RoleId $roleId,
        public DateTimeImmutable $grantedAt,
        public ?DateTimeImmutable $revokedAt,
        public int $ordinal,
    ) {
        if ($ordinal < 0) {
            throw InvalidHistoricalAccountPersistenceState::field('role_assignment.ordinal');
        }
        if ($revokedAt !== null && $revokedAt < $grantedAt) {
            throw InvalidHistoricalAccountPersistenceState::field('role_assignment.revoked_at');
        }
    }
}
