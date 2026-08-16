<?php

namespace App\Application\PublicMediaBinaryDelivery;

final readonly class PublicMediaBinaryContentResult
{
    /** @param resource|null $stream */
    public function __construct(
        public PublicMediaBinaryContentStatus $status,
        public mixed $stream = null,
    ) {}
}
