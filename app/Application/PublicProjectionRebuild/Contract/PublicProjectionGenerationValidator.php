<?php

namespace App\Application\PublicProjectionRebuild\Contract;

use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationValidation;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface PublicProjectionGenerationValidator
{
    public function validate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationValidation;
}
