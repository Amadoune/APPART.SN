<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;

final readonly class EstablishmentName
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (! is_string($value) || mb_strlen($value) < 2 || mb_strlen($value) > 160) {
            throw InvalidProfessionalValue::field('establishment_name');
        }

        return new self($value);
    }
}
