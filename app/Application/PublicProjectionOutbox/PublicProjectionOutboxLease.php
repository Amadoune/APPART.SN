<?php

namespace App\Application\PublicProjectionOutbox;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PublicProjectionOutboxLease
{
    public function __construct(public PublicProjectionOutboxClaimOwnerId $ownerId, public DateTimeImmutable $claimedAt, public DateTimeImmutable $expiresAt)
    {
        if ($expiresAt <= $claimedAt) {
            throw new InvalidArgumentException('Public Projection Outbox lease must have a positive duration.');
        }
    }

    public function isExpiredAt(DateTimeImmutable $at): bool
    {
        return $at >= $this->expiresAt;
    }
}
