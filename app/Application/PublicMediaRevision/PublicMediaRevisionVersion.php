<?php

namespace App\Application\PublicMediaRevision;

use InvalidArgumentException;

final readonly class PublicMediaRevisionVersion
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidArgumentException('A public media revision version must be positive.');
        }

        return new self($value);
    }

    public function compareTo(self $other): PublicMediaRevisionRelation
    {
        if ($this->value < $other->value) {
            return PublicMediaRevisionRelation::Older;
        }
        if ($this->value > $other->value) {
            return PublicMediaRevisionRelation::Newer;
        }

        return PublicMediaRevisionRelation::Equal;
    }
}
