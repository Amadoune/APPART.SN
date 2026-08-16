<?php

namespace App\Application\PublicMediaBinaryDelivery;

final readonly class PublicMediaBinarySourceResult
{
    public function __construct(
        public PublicMediaBinarySourceStatus $status,
        public ?PublicMediaBinarySource $source = null,
    ) {}
}
