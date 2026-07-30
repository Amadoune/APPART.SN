<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class InvalidPropertyValue extends RealEstateCatalogException
{
    public static function field(string $field): self
    {
        return new self("Invalid value for {$field}.");
    }
}
