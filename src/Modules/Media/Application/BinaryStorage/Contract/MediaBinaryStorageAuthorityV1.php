<?php

namespace Appart\Modules\Media\Application\BinaryStorage\Contract;

use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryStorageResult;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;

interface MediaBinaryStorageAuthorityV1
{
    /** @param resource $stream */
    public function store(MediaBinaryWriteRequest $request, mixed $stream): MediaBinaryStorageResult;
}
