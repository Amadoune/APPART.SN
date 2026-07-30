<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidHistoricalAccountPersistenceState;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use DateTimeImmutable;
use LogicException;

final readonly class VerificationPersistenceSnapshotV1
{
    public function __construct(
        public VerificationChannel $channel,
        public SensitivePersistenceValueV1 $verificationToken,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $verifiedAt,
    ) {
        if ($expiresAt <= $issuedAt) {
            throw InvalidHistoricalAccountPersistenceState::field('verification.expires_at');
        }
        if ($verifiedAt !== null && ($verifiedAt < $issuedAt || $verifiedAt >= $expiresAt)) {
            throw InvalidHistoricalAccountPersistenceState::field('verification.verified_at');
        }
    }

    public function __serialize(): array
    {
        throw new LogicException('Verification persistence snapshots cannot be serialized.');
    }

    public function __debugInfo(): array
    {
        return [
            'channel' => $this->channel,
            'verificationToken' => '[REDACTED]',
            'issuedAt' => $this->issuedAt,
            'expiresAt' => $this->expiresAt,
            'verifiedAt' => $this->verifiedAt,
        ];
    }
}
