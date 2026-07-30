<?php

namespace App\Application\ProjectionRuntimeSource\Contract;

use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyResult;
use App\Application\PublicProjectionUpdater\Contract\PublicListingProjectionSource;

interface InspectablePublicListingProjectionSource extends PublicListingProjectionSource
{
    public function inspect(string $listingId): ProjectionSourceAssemblyResult;
}
