<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

final readonly class MediaBinaryStorageResult
{
    public function __construct(
        public MediaBinaryStorageStatus $status,
        public ?MediaBinaryObject $object = null,
    ) {}
}
