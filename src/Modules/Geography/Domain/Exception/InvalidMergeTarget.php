<?php

namespace Appart\Modules\Geography\Domain\Exception;

final class InvalidMergeTarget extends GeographyException
{
    public static function inactive(): self
    {
        return new self('A place can only be merged into an active place.');
    }

    public static function differentType(): self
    {
        return new self('A place can only be merged into a place of the same type.');
    }

    public static function differentCountry(): self
    {
        return new self('A place can only be merged into a place in the same country.');
    }
}
