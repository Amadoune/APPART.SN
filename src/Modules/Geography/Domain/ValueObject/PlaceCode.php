<?php

namespace Appart\Modules\Geography\Domain\ValueObject;

use Appart\Modules\Geography\Domain\Exception\InvalidPlaceCode;

final readonly class PlaceCode
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^[A-Z0-9][A-Z0-9._-]{0,31}$/', $value) !== 1) {
            throw InvalidPlaceCode::fromValue($value);
        }

        return new self($value);
    }
}
