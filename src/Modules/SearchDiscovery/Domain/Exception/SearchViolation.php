<?php

namespace Appart\Modules\SearchDiscovery\Domain\Exception;

final class SearchViolation extends SearchException
{
    public static function duplicate(string $type): self
    {
        return new self("Duplicate {$type}.");
    }

    public static function missing(): self
    {
        return new self('Search document not found.');
    }

    public static function removed(): self
    {
        return new self('Search document is logically removed.');
    }

    public static function unchanged(): self
    {
        return new self('Search projection is unchanged.');
    }
}
