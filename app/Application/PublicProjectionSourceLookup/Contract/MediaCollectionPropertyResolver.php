<?php

namespace App\Application\PublicProjectionSourceLookup\Contract;

use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyResolution;

interface MediaCollectionPropertyResolver
{
    public function resolve(string $mediaCollectionId): MediaCollectionPropertyResolution;
}
