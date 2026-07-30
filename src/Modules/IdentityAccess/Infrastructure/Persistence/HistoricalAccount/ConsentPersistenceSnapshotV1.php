<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidHistoricalAccountPersistenceState;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use DateTimeImmutable;

final readonly class ConsentPersistenceSnapshotV1
{
    public function __construct(
        public ConsentPurpose $purpose,
        public DateTimeImmutable $grantedAt,
        public ?DateTimeImmutable $withdrawnAt,
        public int $ordinal,
    ) {
        if ($ordinal < 0) {
            throw InvalidHistoricalAccountPersistenceState::field('consent.ordinal');
        }
        if ($withdrawnAt !== null && $withdrawnAt < $grantedAt) {
            throw InvalidHistoricalAccountPersistenceState::field('consent.withdrawn_at');
        }
    }
}
