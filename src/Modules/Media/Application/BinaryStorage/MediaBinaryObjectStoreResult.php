<?php

namespace Appart\Modules\Media\Application\BinaryStorage;

final readonly class MediaBinaryObjectStoreResult
{
    public function __construct(
        public MediaBinaryObjectStoreStatus $status,
        public ?MediaBinaryObject $object = null,
    ) {}
}
