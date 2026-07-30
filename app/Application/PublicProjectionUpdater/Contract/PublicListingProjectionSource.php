<?php

namespace App\Application\PublicProjectionUpdater\Contract;

use App\Application\PublicProjectionUpdater\PublicListingProjectionSources;

interface PublicListingProjectionSource
{
    public function findByListingId(string $listingId): ?PublicListingProjectionSources;
}
