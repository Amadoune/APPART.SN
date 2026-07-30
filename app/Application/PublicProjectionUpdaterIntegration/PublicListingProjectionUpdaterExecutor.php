<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateResult;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;

final readonly class PublicListingProjectionUpdaterExecutor implements PublicProjectionUpdateExecutor
{
    public function __construct(private PublicListingProjectionUpdater $updater) {}

    public function update(string $listingId): PublicListingProjectionUpdateResult
    {
        return $this->updater->update($listingId);
    }
}
