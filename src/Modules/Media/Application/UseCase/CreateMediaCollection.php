<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Domain\Exception\PropertyUnavailable;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

final readonly class CreateMediaCollection
{
    public function __construct(private MediaCollectionRegistry $collections, private PropertyCatalog $properties) {}

    public function execute(MediaCollectionId $id, PropertyId $propertyId, DateTimeImmutable $at): MediaCollection
    {
        if (! $this->properties->exists($propertyId)) {
            throw new PropertyUnavailable;
        }
        $collection = MediaCollection::create($id, $propertyId, $at);
        $this->collections->add($collection);

        return $collection;
    }
}
