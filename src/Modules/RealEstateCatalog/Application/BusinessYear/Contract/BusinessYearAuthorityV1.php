<?php

namespace Appart\Modules\RealEstateCatalog\Application\BusinessYear\Contract;

use Appart\Modules\RealEstateCatalog\Application\BusinessYear\BusinessYearResolutionResult;
use Appart\Modules\RealEstateCatalog\Application\BusinessYear\PropertyDecisionOccurredAt;

interface BusinessYearAuthorityV1
{
    public function resolve(PropertyDecisionOccurredAt $occurredAt): BusinessYearResolutionResult;
}
