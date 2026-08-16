<?php

namespace App\Application\PublicMediaMaterialization;

final readonly class PublicMediaOwnerSourceV2
{
    /** @param list<PublicMediaOwnerItemV2> $items */
    public function __construct(
        public string $listingId,
        public int $publicationVersion,
        public string $listingState,
        public string $mediaCollectionId,
        public int $collectionVersion,
        public array $items,
    ) {}
}
