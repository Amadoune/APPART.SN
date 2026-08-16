<?php

namespace App\Application\PublicMediaBinaryDelivery\Contract;

use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryContentResult;

interface PublicMediaBinaryContentReader
{
    public function read(string $ownerId, string $mediaId, string $checksum, int $bytes): PublicMediaBinaryContentResult;
}
