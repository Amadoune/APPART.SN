<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class ConcurrentPropertyModification extends RealEstateCatalogException
{
    public function __construct()
    {
        parent::__construct('The property changed concurrently.');
    }
}
