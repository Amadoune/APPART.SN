<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class PropertyIdConflict extends RealEstateCatalogException
{
    public function __construct()
    {
        parent::__construct('The property identity already exists.');
    }
}
