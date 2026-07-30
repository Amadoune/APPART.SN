<?php

namespace App\Infrastructure\PublicProjectionSourceLookup;

use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyResolution;
use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyStatus;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Throwable;

final readonly class RegistryMediaCollectionPropertyResolver implements MediaCollectionPropertyResolver
{
    public function __construct(private MediaCollectionRegistry $mediaCollections) {}

    public function resolve(string $mediaCollectionId): MediaCollectionPropertyResolution
    {
        try {
            $collection = $this->mediaCollections->find(MediaCollectionId::fromString($mediaCollectionId));
        } catch (Throwable) {
            return new MediaCollectionPropertyResolution(MediaCollectionPropertyStatus::Corrupted);
        }

        return $collection === null
            ? new MediaCollectionPropertyResolution(MediaCollectionPropertyStatus::Missing)
            : new MediaCollectionPropertyResolution(MediaCollectionPropertyStatus::Found, $collection->propertyId()->value);
    }
}
