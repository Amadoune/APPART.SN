<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;

final readonly class PhoneNumber
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/[\s().-]+/', '', trim($value));
        if (! is_string($value) || preg_match('/^\+[1-9][0-9]{7,14}$/', $value) !== 1) {
            throw InvalidIdentityValue::forField('phone');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
