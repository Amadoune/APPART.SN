<?php

namespace Appart\Modules\Geography\Application\GeographySelection\Contract;

use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionResult;

interface GeographySelectionReaderV1
{
    public function read(GeographySelectionQuery $query): GeographySelectionResult;
}
