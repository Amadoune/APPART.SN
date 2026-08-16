<?php

namespace App\Application\PublicMediaBinaryDelivery;

final readonly class PublicMediaBinaryResult
{
    /** @param resource|null $stream */
    public function __construct(
        public PublicMediaBinaryStatus $status,
        public mixed $stream = null,
        public ?string $contentType = null,
        public ?int $bytes = null,
        public ?string $checksum = null,
    ) {}
}
