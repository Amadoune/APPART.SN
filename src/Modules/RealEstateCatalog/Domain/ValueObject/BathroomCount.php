<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class BathroomCount
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 0 || $value > 1_000) {
            throw InvalidPropertyValue::field('bathroom_count');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
