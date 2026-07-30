<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;

final readonly class RoleId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-z][a-z0-9_-]{1,63}$/', $value) !== 1) {
            throw InvalidIdentityValue::forField('role_id');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
