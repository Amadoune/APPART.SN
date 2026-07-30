<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;

final readonly class RemoveMedia extends MediaCollectionUseCase
{
    public function execute(MediaCollectionId $collectionId, MediaId $id, ?MediaId $replacementPrimaryId, DateTimeImmutable $at): MediaCollection
    {
        $collection = $this->collection($collectionId);
        $version = $collection->version();
        $collection->remove($id, $replacementPrimaryId, $at);

        return $this->save($collection, $version);
    }
}
