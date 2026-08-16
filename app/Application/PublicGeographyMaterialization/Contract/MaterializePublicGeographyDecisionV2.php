<?php

namespace App\Application\PublicGeographyMaterialization\Contract;

use App\Application\PublicGeographyMaterialization\PublicGeographyMaterializationResult;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface MaterializePublicGeographyDecisionV2
{
    public function materialize(ListingId $listingId): PublicGeographyMaterializationResult;
}
