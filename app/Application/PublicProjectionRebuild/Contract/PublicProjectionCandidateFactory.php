<?php

namespace App\Application\PublicProjectionRebuild\Contract;

use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface PublicProjectionCandidateFactory
{
    public function rebuild(string $listingId, PublicProjectionGenerationId $generationId): ?PublicListingProjectionRecord;
}
