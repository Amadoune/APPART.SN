<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class SurfaceArea
{
    private function __construct(public int $squareMeters) {}

    public static function fromSquareMeters(int $value): self
    {
        if ($value <= 0 || $value > 10_000_000) {
            throw InvalidPropertyValue::field('surface_area');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->squareMeters === $other->squareMeters;
    }
}
