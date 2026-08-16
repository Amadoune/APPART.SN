<?php

namespace App\Application\PublicMediaMaterialization\Contract;

use App\Application\PublicMediaMaterialization\PublicMediaOwnerSourceResult;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface PublicMediaOwnerSourceReaderV2
{
    public function read(ListingId $listingId): PublicMediaOwnerSourceResult;
}
