<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class BusinessYear
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1800 || $value > 9999) {
            throw InvalidPropertyValue::field('business_year');
        }

        return new self($value);
    }
}
