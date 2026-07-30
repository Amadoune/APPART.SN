<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class PropertyReferenceConflict extends RealEstateCatalogException
{
    public function __construct()
    {
        parent::__construct('The property reference already exists.');
    }
}
