<?php

namespace App\Application\ProjectionRebuildRuntimeSource;

use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;

final readonly class CandidateBuildResult
{
    private function __construct(
        public string $listingId,
        public CandidateBuildStatus $status,
        public ?PublicListingProjectionRecord $record,
        public ?ProjectionSourceAssemblyStatus $sourceStatus,
        public ?PublicProjectionPromotionReadiness $readiness,
    ) {}

    public static function built(string $listingId, PublicListingProjectionRecord $record): self
    {
        return new self($listingId, CandidateBuildStatus::Built, $record, ProjectionSourceAssemblyStatus::Found, PublicProjectionPromotionReadiness::Ready);
    }

    public static function blocked(
        string $listingId,
        CandidateBuildStatus $status,
        ?ProjectionSourceAssemblyStatus $sourceStatus = null,
        ?PublicProjectionPromotionReadiness $readiness = null,
    ): self {
        return new self($listingId, $status, null, $sourceStatus, $readiness);
    }
}
