<?php

namespace Appart\Modules\Geography\Domain\ValueObject;

use Appart\Modules\Geography\Domain\Exception\InvalidCountryCode;

final readonly class CountryCode
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^[A-Z]{2}$/', $value) !== 1) {
            throw InvalidCountryCode::fromValue($value);
        }

        return new self($value);
    }
}
