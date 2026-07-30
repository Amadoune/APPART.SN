<?php

namespace Appart\Modules\ModerationReports\Application\Contract;

use Appart\Modules\ModerationReports\Domain\ValueObject\ListingEligibility;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;

interface ListingCatalog
{
    public function eligibilityOf(ListingId $id): ListingEligibility;
}
