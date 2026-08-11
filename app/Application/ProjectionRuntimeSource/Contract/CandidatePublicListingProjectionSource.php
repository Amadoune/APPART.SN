<?php

namespace App\Application\ProjectionRuntimeSource\Contract;

use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyResult;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface CandidatePublicListingProjectionSource extends InspectablePublicListingProjectionSource
{
    public function inspectForGeneration(string $listingId, PublicProjectionGenerationId $generationId): ProjectionSourceAssemblyResult;
}
