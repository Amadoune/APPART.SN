<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;

final readonly class MandateRole
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-z][a-z0-9_]{2,63}$/', $value) !== 1) {
            throw InvalidProfessionalValue::field('mandate_role');
        }

        return new self($value);
    }
}
