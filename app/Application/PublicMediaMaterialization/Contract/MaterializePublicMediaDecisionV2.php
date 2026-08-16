<?php

namespace App\Application\PublicMediaMaterialization\Contract;

use App\Application\PublicMediaMaterialization\PublicMediaMaterializationResult;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface MaterializePublicMediaDecisionV2
{
    public function materialize(ListingId $listingId): PublicMediaMaterializationResult;
}
