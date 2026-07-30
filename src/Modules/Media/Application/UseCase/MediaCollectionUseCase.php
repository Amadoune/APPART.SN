<?php

namespace Appart\Modules\Media\Application\UseCase;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Exception\MediaCollectionNotFound;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;

abstract readonly class MediaCollectionUseCase
{
    public function __construct(protected MediaCollectionRegistry $collections) {}

    protected function collection(MediaCollectionId $id): MediaCollection
    {
        return $this->collections->find($id) ?? throw new MediaCollectionNotFound;
    }

    protected function save(MediaCollection $collection, int $version): MediaCollection
    {
        $this->collections->save($collection, $version);

        return $collection;
    }
}
