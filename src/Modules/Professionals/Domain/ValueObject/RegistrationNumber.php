<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;

final readonly class RegistrationNumber
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtoupper(preg_replace('/\s+/', '', trim($value)) ?? '');
        if (preg_match('/^[A-Z0-9][A-Z0-9.\/-]{4,63}$/', $value) !== 1) {
            throw InvalidProfessionalValue::field('registration_number');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
