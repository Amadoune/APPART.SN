<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class PropertyNotFound extends RealEstateCatalogException
{
    public function __construct()
    {
        parent::__construct('The property was not found.');
    }
}
