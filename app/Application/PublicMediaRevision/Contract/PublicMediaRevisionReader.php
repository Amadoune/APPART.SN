<?php

namespace App\Application\PublicMediaRevision\Contract;

use App\Application\PublicMediaRevision\PublicMediaRevision;

interface PublicMediaRevisionReader
{
    public function stableRevisionForMediaCollection(string $mediaCollectionId): ?PublicMediaRevision;
}
