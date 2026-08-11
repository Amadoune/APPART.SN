<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

final readonly class MediaBinaryObject
{
    public function __construct(
        public string $ownerId,
        public string $assetId,
        public string $storageKey,
        public string $checksum,
        public int $bytes,
    ) {}
}
