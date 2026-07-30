<?php

namespace Appart\Modules\Media\Application\Attachment;

final readonly class MediaAttachmentIntent
{
    public function __construct(
        public string $intentId,
        public string $checksum,
        public string $collectionId,
        public string $propertyId,
        public string $mediaId,
        public ?int $aggregateVersion = null,
    ) {}
}
