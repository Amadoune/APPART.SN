<?php

namespace App\Application\PublicMediaBinaryDelivery;

final readonly class PublicMediaBinarySource
{
    public function __construct(
        public string $mediaId,
        public string $ownerId,
        public string $mediaStatus,
        public string $assetState,
        public int $assetVersion,
        public string $contentType,
        public string $checksum,
        public int $bytes,
        public string $attachmentResult,
        public bool $listingPublished,
    ) {}
}
