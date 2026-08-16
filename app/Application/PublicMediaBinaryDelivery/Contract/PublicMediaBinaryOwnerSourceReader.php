<?php

namespace App\Application\PublicMediaBinaryDelivery\Contract;

use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySourceResult;

interface PublicMediaBinaryOwnerSourceReader
{
    public function read(string $mediaId): PublicMediaBinarySourceResult;
}
