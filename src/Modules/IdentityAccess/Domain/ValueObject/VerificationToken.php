<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;

final readonly class VerificationToken
{
    private function __construct(private VerificationChannel $channel, private string $secret) {}

    public static function forChannel(VerificationChannel $channel, string $value): self
    {
        if (strlen($value) < 32 || strlen($value) > 128 || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            throw InvalidIdentityValue::forField('verification_token');
        }

        return new self($channel, $value);
    }

    public function matches(self $candidate): bool
    {
        return $this->channel === $candidate->channel && hash_equals($this->secret, $candidate->secret);
    }

    public function belongsTo(VerificationChannel $channel): bool
    {
        return $this->channel === $channel;
    }

    public function persistenceValue(): SensitivePersistenceValueV1
    {
        return SensitivePersistenceValueV1::fromSecret($this->secret);
    }

    public function __serialize(): array
    {
        throw new \LogicException('Verification tokens cannot be serialized from the domain.');
    }

    public function __debugInfo(): array
    {
        return ['channel' => $this->channel->value, 'secret' => '[REDACTED]'];
    }
}
