<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;

final readonly class EmailAddress
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = mb_strtolower(trim($value));
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false || mb_strlen($value) > 254) {
            throw InvalidIdentityValue::forField('email');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
