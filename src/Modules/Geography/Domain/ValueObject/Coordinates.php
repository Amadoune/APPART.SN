<?php

namespace Appart\Modules\Geography\Domain\ValueObject;

use Appart\Modules\Geography\Domain\Exception\InvalidCoordinates;

final readonly class Coordinates
{
    private function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public static function fromDecimal(float $latitude, float $longitude): self
    {
        if (! is_finite($latitude) || ! is_finite($longitude) || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw InvalidCoordinates::outsideWorldBounds($latitude, $longitude);
        }

        return new self($latitude, $longitude);
    }
}
