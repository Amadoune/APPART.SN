<?php

namespace Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support;

use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateResult;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;

final class FakePublicProjectionUpdateExecutor implements PublicProjectionUpdateExecutor
{
    public int $calls = 0;

    public function __construct(public PublicListingProjectionUpdateOutcome $outcome) {}

    public function update(string $listingId): PublicListingProjectionUpdateResult
    {
        $this->calls++;

        return new PublicListingProjectionUpdateResult($this->outcome);
    }
}
