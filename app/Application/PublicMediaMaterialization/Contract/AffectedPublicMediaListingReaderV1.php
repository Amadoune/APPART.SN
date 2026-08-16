<?php

namespace App\Application\PublicMediaMaterialization\Contract;

interface AffectedPublicMediaListingReaderV1
{
    public function listingIdForMedia(string $mediaId): ?string;
}
