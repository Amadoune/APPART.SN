<?php

namespace Appart\Modules\SearchDiscovery\Domain\Exception;

final class InvalidSearchValue extends SearchException
{
    public static function field(string $field): self
    {
        return new self("Invalid search value: {$field}.");
    }
}
