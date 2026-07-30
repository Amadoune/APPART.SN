<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class ConstructionYear
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1800 || $value > 9999) {
            throw InvalidPropertyValue::field('construction_year');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
