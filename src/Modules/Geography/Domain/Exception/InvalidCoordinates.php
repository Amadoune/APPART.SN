<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class InvalidCoordinates extends GeographyException
{
    public static function outsideWorldBounds(float $latitude, float $longitude): self
    {
        return new self(sprintf('Coordinates %.8F, %.8F are outside world bounds.', $latitude, $longitude));
    }
}
