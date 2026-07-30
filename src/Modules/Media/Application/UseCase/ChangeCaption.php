<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;

final readonly class ChangeCaption extends MediaCollectionUseCase
{
    public function execute(MediaCollectionId $collectionId, MediaId $id, MediaCaption $caption, DateTimeImmutable $at): MediaCollection
    {
        $collection = $this->collection($collectionId);
        $version = $collection->version();
        $collection->changeCaption($id, $caption, $at);

        return $this->save($collection, $version);
    }
}
