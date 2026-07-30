<?php

namespace Appart\Modules\IdentityAccess\Domain\Model;

use Appart\Modules\IdentityAccess\Domain\Exception\TemporalConsistencyViolation;
use Appart\Modules\IdentityAccess\Domain\ValueObject\ConsentPurpose;
use DateTimeImmutable;

final class Consent
{
    private ?DateTimeImmutable $withdrawnAt = null;

    public function __construct(public readonly ConsentPurpose $purpose, public readonly DateTimeImmutable $grantedAt) {}

    public function withdraw(DateTimeImmutable $withdrawnAt): void
    {
        if ($this->withdrawnAt !== null || $withdrawnAt < $this->grantedAt) {
            throw TemporalConsistencyViolation::nonIncreasingTime();
        }
        $this->withdrawnAt = $withdrawnAt;
    }

    public function isGranted(): bool
    {
        return $this->withdrawnAt === null;
    }

    public static function reconstitute(ConsentPurpose $purpose, DateTimeImmutable $grantedAt, ?DateTimeImmutable $withdrawnAt): self
    {
        $consent = new self($purpose, $grantedAt);
        if ($withdrawnAt !== null) {
            if ($withdrawnAt < $grantedAt) {
                throw TemporalConsistencyViolation::nonIncreasingTime();
            }
            $consent->withdrawnAt = $withdrawnAt;
        }

        return $consent;
    }

    /** @return array{purpose: ConsentPurpose, grantedAt: DateTimeImmutable, withdrawnAt: ?DateTimeImmutable, ordinal: int} */
    public function persistenceState(int $ordinal): array
    {
        return ['purpose' => $this->purpose, 'grantedAt' => $this->grantedAt, 'withdrawnAt' => $this->withdrawnAt, 'ordinal' => $ordinal];
    }
}
