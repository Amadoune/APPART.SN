<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\Persistence\SensitivePersistenceValueV1;

final readonly class PasswordHash
{
    private function __construct(private string $encoded) {}

    public static function fromString(string $value): self
    {
        if (strlen($value) > 255 || preg_match('/^\$[A-Za-z0-9][A-Za-z0-9-]{1,31}(?:\$[^$\s]{1,128}){2,5}$/D', $value) !== 1) {
            throw InvalidIdentityValue::forField('password_hash');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->encoded, $other->encoded);
    }

    public function persistenceValue(): SensitivePersistenceValueV1
    {
        return SensitivePersistenceValueV1::fromSecret($this->encoded);
    }

    public function __serialize(): array
    {
        throw new \LogicException('Password hashes cannot be serialized from the domain.');
    }

    public function __debugInfo(): array
    {
        return ['encoded' => '[REDACTED]'];
    }
}
