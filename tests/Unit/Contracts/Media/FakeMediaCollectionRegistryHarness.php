<?php

namespace Tests\Unit\Contracts\Media;

use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Domain\Model\MediaCollection;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaSource;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Tests\Unit\Modules\Media\Support\FakeMediaCollectionRegistry;

class FakeMediaCollectionRegistryHarness implements MediaCollectionRegistryHarness
{
    public function freshRegistry(): MediaCollectionRegistry
    {
        return new FakeMediaCollectionRegistry;
    }

    public function emptyCollection(?MediaCollectionId $id = null): MediaCollection
    {
        return MediaCollection::create($id ?? $this->primaryId(), PropertyId::fromString('33000000-0000-4000-8000-000000000001'), $this->at(0));
    }

    public function primaryId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('34000000-0000-4000-8000-000000000001');
    }

    public function distinctId(): MediaCollectionId
    {
        return MediaCollectionId::fromString('34000000-0000-4000-8000-000000000002');
    }

    public function firstMediaId(): MediaId
    {
        return MediaId::fromString('34000000-0000-4000-8000-000000000101');
    }

    public function secondMediaId(): MediaId
    {
        return MediaId::fromString('34000000-0000-4000-8000-000000000102');
    }

    public function addMedia(MediaCollection $collection, MediaId $id, int $order): void
    {
        $collection->add($id, MediaType::Image, MediaChecksum::fromSha256(str_repeat((string) $order, 64)), MediaOrder::fromInt($order), $order === 1 ? MediaCaption::fromString('Contract primary media') : null, $order === 1 ? MediaSource::Owner : MediaSource::Professional, $this->at($order));
    }

    public function mutate(MediaCollection $collection): void
    {
        $collection->changeCaption($this->firstMediaId(), MediaCaption::fromString('Updated contract caption'), $this->at(3));
    }

    public function archiveSecond(MediaCollection $collection): void
    {
        $collection->archive($this->secondMediaId(), null, $this->at(4));
    }

    public function failNextWrite(MediaCollectionRegistry $registry): void
    {
        Assert::assertInstanceOf(FakeMediaCollectionRegistry::class, $registry);
        $registry->failNextSave();
    }

    private function at(int $minute): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('2026-07-18T10:%02d:00+00:00', $minute));
    }
}
