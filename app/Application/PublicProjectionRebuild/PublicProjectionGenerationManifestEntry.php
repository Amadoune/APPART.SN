<?php

namespace App\Application\PublicProjectionRebuild;

use App\Application\PublicProjectionStore\PublicProjectionWatermark;
use InvalidArgumentException;

final readonly class PublicProjectionGenerationManifestEntry
{
    public function __construct(public string $listingId, public PublicProjectionWatermark $watermark)
    {
        if (trim($listingId) === '') {
            throw new InvalidArgumentException('A generation manifest entry requires a listing identity.');
        }
    }
}
