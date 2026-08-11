<?php

namespace Appart\Modules\Media\Application\BinaryStorage\Contract;

use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryObjectStoreResult;
use Appart\Modules\Media\Application\BinaryStorage\MediaBinaryWriteRequest;

interface MediaBinaryObjectStore
{
    /** @param resource $stream */
    public function store(MediaBinaryWriteRequest $request, mixed $stream): MediaBinaryObjectStoreResult;

    public function inspect(string $ownerId, string $assetId): MediaBinaryObjectStoreResult;

    public function delete(string $ownerId, string $assetId): void;
}
