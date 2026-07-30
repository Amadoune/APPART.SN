<?php

namespace App\Application\PublicProjectionUpdater;

use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWriteResult;

final readonly class PublicListingProjectionUpdateResult
{
    public function __construct(
        public PublicListingProjectionUpdateOutcome $outcome,
        public ?PublicProjectionPromotionReadiness $readiness = null,
        public ?PublicProjectionWriteResult $writeResult = null,
    ) {}
}
