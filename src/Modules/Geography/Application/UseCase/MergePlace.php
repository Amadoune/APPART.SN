<?php

namespace Appart\Modules\Geography\Application\UseCase;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Exception\PlaceNotFound;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use DateTimeImmutable;

final readonly class MergePlace
{
    public function __construct(private PlaceRegistry $registry) {}

    public function execute(PlaceId $sourceId, PlaceId $targetId, DateTimeImmutable $occurredAt): Place
    {
        $source = $this->registry->find($sourceId) ?? throw PlaceNotFound::forId($sourceId);
        $target = $this->registry->find($targetId) ?? throw PlaceNotFound::forId($targetId);

        $expectedVersion = $source->version();
        $source->mergeInto($target, $occurredAt);
        $this->registry->save($source, $expectedVersion);

        return $source;
    }
}
