<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use DateTimeImmutable;

final readonly class AddMedia extends MediaCollectionUseCase
{
    public function execute(MediaCollectionId $collectionId, MediaId $id, MediaType $type, MediaChecksum $checksum, MediaOrder $order, ?MediaCaption $caption, MediaSource $source, DateTimeImmutable $at): MediaCollection
    {
        $collection = $this->collection($collectionId);
        $version = $collection->version();
        $collection->add($id, $type, $checksum, $order, $caption, $source, $at);
        $this->collections->saveWithMediaReservation($collection, $id, $version);

        return $collection;
    }
}
