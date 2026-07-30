<?php

namespace App\Application\PublicGeographyRevision;

use InvalidArgumentException;

final readonly class PublicGeographyRevisionVersion
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidArgumentException('A public geography revision version must be positive.');
        }

        return new self($value);
    }

    public function compareTo(self $other): PublicGeographyRevisionRelation
    {
        if ($this->value < $other->value) {
            return PublicGeographyRevisionRelation::Older;
        }
        if ($this->value > $other->value) {
            return PublicGeographyRevisionRelation::Newer;
        }

        return PublicGeographyRevisionRelation::Equal;
    }
}
