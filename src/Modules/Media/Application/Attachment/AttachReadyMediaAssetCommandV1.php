<?php

namespace Appart\Modules\Media\Application\Attachment;

final readonly class AttachReadyMediaAssetCommandV1
{
    public function __construct(
        public string $intentId,
        public string $intentChecksum,
        public string $collectionId,
        public string $propertyId,
        public string $mediaId,
        public string $contentChecksum,
        public int $order,
        public ?string $caption,
        public string $source,
        public ?int $expectedCollectionVersion,
        public string $occurredAt,
    ) {}
}
