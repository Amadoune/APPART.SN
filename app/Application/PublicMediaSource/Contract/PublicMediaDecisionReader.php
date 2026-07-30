<?php

namespace App\Application\PublicMediaSource\Contract;

use App\Application\PublicMediaSource\PublicMediaReadResult;

interface PublicMediaDecisionReader
{
    public function read(string $mediaCollectionId): PublicMediaReadResult;
}
