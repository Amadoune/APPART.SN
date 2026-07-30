<?php

namespace Appart\Modules\Media\Application\Ownership;

use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;

final readonly class MediaOwnershipResult
{
    private function __construct(
        public PropertyId $propertyId,
        public MediaOwnershipResolution $resolution,
        public ?MediaCollectionId $collectionId,
    ) {}

    public static function found(PropertyId $propertyId, MediaCollectionId $collectionId): self
    {
        return new self($propertyId, MediaOwnershipResolution::Found, $collectionId);
    }

    public static function missing(PropertyId $propertyId): self
    {
        return new self($propertyId, MediaOwnershipResolution::Missing, null);
    }

    public static function ambiguous(PropertyId $propertyId): self
    {
        return new self($propertyId, MediaOwnershipResolution::Ambiguous, null);
    }
}
