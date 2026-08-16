<?php

namespace App\Application\PublicMediaMaterialization;

final readonly class PublicMediaOwnerItemV2
{
    public function __construct(
        public string $mediaId,
        public int $order,
        public bool $primary,
        public int $assetVersion,
        public string $assetState,
        public string $contentChecksum,
        public int $attachmentVersion,
        public string $attachmentChecksum,
    ) {}
}
