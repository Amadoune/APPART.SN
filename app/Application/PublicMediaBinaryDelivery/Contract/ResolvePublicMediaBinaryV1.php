<?php

namespace App\Application\PublicMediaBinaryDelivery\Contract;

use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryResult;

interface ResolvePublicMediaBinaryV1
{
    public function resolve(string $mediaId, int $assetVersion): PublicMediaBinaryResult;
}
