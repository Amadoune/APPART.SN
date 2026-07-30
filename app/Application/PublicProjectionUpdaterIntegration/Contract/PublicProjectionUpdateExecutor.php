<?php

namespace App\Application\PublicProjectionUpdaterIntegration\Contract;

use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateResult;

interface PublicProjectionUpdateExecutor
{
    public function update(string $listingId): PublicListingProjectionUpdateResult;
}
