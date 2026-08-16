<?php

namespace App\Application\PublicGeographyMaterialization;

use App\Application\PublicGeographyMaterialization\Contract\MaterializePublicGeographyDecisionV2;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

final readonly class CatchUpPublicGeographyDecisionV2
{
    public function __construct(private MaterializePublicGeographyDecisionV2 $materializer) {}

    public function catchUp(ListingId $listingId): PublicGeographyMaterializationResult
    {
        return $this->materializer->materialize($listingId);
    }
}
