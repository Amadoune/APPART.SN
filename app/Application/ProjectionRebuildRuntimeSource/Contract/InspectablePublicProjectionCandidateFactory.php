<?php

namespace App\Application\ProjectionRebuildRuntimeSource\Contract;

use App\Application\ProjectionRebuildRuntimeSource\CandidateBuildResult;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionCandidateFactory;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface InspectablePublicProjectionCandidateFactory extends PublicProjectionCandidateFactory
{
    public function inspect(string $listingId, PublicProjectionGenerationId $generationId): CandidateBuildResult;
}
