<?php

namespace Appart\Modules\IdentityAccess\Domain\Model;

use Appart\Modules\IdentityAccess\Domain\Exception\TemporalConsistencyViolation;
use Appart\Modules\IdentityAccess\Domain\Exception\VerificationFailed;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationChannel;
use Appart\Modules\IdentityAccess\Domain\ValueObject\VerificationToken;
use DateTimeImmutable;

final class Verification
{
    private ?DateTimeImmutable $verifiedAt = null;

    public function __construct(
        public readonly VerificationChannel $channel,
        private VerificationToken $token,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
    ) {
        $this->guardChallenge($token, $issuedAt, $expiresAt);
    }

    public function verify(VerificationToken $candidate, DateTimeImmutable $verifiedAt): void
    {
        if ($this->verifiedAt !== null) {
            throw VerificationFailed::alreadyVerified();
        }
        if ($verifiedAt < $this->issuedAt || $verifiedAt >= $this->expiresAt) {
            throw VerificationFailed::expiredToken();
        }
        if (! $this->token->matches($candidate)) {
            throw VerificationFailed::invalidToken();
        }
        $this->verifiedAt = $verifiedAt;
    }

    public function replace(VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        if ($this->verifiedAt !== null) {
            throw VerificationFailed::alreadyVerified();
        }
        if ($issuedAt <= $this->issuedAt) {
            throw TemporalConsistencyViolation::nonIncreasingTime();
        }
        $this->guardChallenge($token, $issuedAt, $expiresAt);
        $this->token = $token;
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
    }

    public function isVerified(): bool
    {
        return $this->verifiedAt !== null;
    }

    public function verifiedAt(): ?DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public static function reconstitute(
        VerificationChannel $channel,
        VerificationToken $token,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $verifiedAt,
    ): self {
        $verification = new self($channel, $token, $issuedAt, $expiresAt);
        if ($verifiedAt !== null) {
            if ($verifiedAt < $issuedAt || $verifiedAt >= $expiresAt) {
                throw VerificationFailed::expiredToken();
            }
            $verification->verifiedAt = $verifiedAt;
        }

        return $verification;
    }

    /** @return array{channel: VerificationChannel, verificationToken: SensitivePersistenceValueV1, issuedAt: DateTimeImmutable, expiresAt: DateTimeImmutable, verifiedAt: ?DateTimeImmutable} */
    public function persistenceState(): array
    {
        return ['channel' => $this->channel, 'verificationToken' => $this->token->persistenceValue(), 'issuedAt' => $this->issuedAt, 'expiresAt' => $this->expiresAt, 'verifiedAt' => $this->verifiedAt];
    }

    private function guardChallenge(VerificationToken $token, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        if (! $token->belongsTo($this->channel)) {
            throw VerificationFailed::wrongChannel();
        }
        if ($expiresAt <= $issuedAt) {
            throw VerificationFailed::expiredToken();
        }
    }
}
