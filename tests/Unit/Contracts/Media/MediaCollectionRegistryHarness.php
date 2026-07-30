<?php

namespace Tests\Unit\Contracts\Media;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;

interface MediaCollectionRegistryHarness
{
    public function freshRegistry(): MediaCollectionRegistry;

    public function emptyCollection(?MediaCollectionId $id = null): MediaCollection;

    public function primaryId(): MediaCollectionId;

    public function distinctId(): MediaCollectionId;

    public function firstMediaId(): MediaId;

    public function secondMediaId(): MediaId;

    public function addMedia(MediaCollection $collection, MediaId $id, int $order): void;

    public function mutate(MediaCollection $collection): void;

    public function archiveSecond(MediaCollection $collection): void;

    public function failNextWrite(MediaCollectionRegistry $registry): void;
}
