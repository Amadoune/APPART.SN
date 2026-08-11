<?php

namespace Appart\Modules\Media\Application\ReadyAsset;

final readonly class MediaAssetReadinessResult
{
    public function __construct(
        public MediaAssetReadinessStatus $status,
        public ?string $checksum = null,
        public ?int $version = null,
    ) {}
}
