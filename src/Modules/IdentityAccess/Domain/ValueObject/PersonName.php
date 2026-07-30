<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;

final readonly class PersonName
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (! is_string($value) || mb_strlen($value) < 2 || mb_strlen($value) > 120) {
            throw InvalidIdentityValue::forField('person_name');
        }

        return new self($value);
    }
}
