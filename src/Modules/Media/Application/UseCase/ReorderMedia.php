<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;

final readonly class ReorderMedia extends MediaCollectionUseCase
{
    /** @param list<MediaId> $orderedMediaIds */
    public function execute(MediaCollectionId $collectionId, array $orderedMediaIds, DateTimeImmutable $at): MediaCollection
    {
        $collection = $this->collection($collectionId);
        $version = $collection->version();
        $collection->reorder($orderedMediaIds, $at);

        return $this->save($collection, $version);
    }
}
