<?php

namespace App\Application\ProjectionRuntimeSource;

use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use App\Application\PublicProjectionUpdater\PublicListingProjectionSources;

final readonly class ProjectionSourceAssemblyResult
{
    private function __construct(
        public string $listingId,
        public ProjectionSourceAssemblyStatus $status,
        public ?PublicListingProjectionSources $sources,
        public ?PublicProjectionWatermark $watermark,
        public ?PublicProjectionPromotionReadiness $readiness,
    ) {}

    public static function found(string $listingId, PublicListingProjectionSources $sources): self
    {
        $watermark = new PublicProjectionWatermark(
            $sources->listing->version(),
            $sources->property->version(),
            $sources->media->version(),
            $sources->searchVersion,
            $sources->contentSeoVersion,
            $sources->publicGeographyVersion,
            $sources->publicMediaVersion,
        );

        return new self($listingId, ProjectionSourceAssemblyStatus::Found, $sources, $watermark, $watermark->readiness());
    }

    public static function blocked(string $listingId, ProjectionSourceAssemblyStatus $status): self
    {
        return new self($listingId, $status, null, null, null);
    }
}
