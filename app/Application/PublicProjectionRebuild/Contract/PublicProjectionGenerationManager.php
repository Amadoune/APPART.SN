<?php

namespace App\Application\PublicProjectionRebuild\Contract;

use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface PublicProjectionGenerationManager
{
    public function createCandidate(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition;

    public function activate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationTransition;

    public function rollback(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition;
}
