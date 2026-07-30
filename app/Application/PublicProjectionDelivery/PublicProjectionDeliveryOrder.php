<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryOrder
{
    public function __construct(public int $aggregateVersion, public PublicProjectionDeliveryEventIndex $eventIndex)
    {
        if ($aggregateVersion < 1) {
            throw new InvalidArgumentException('Invalid Public Projection Delivery aggregate version.');
        }
    }

    public function relationTo(self $other, bool $sameAggregate): PublicProjectionDeliveryOrderRelation
    {
        if (! $sameAggregate) {
            return PublicProjectionDeliveryOrderRelation::Invalid;
        }
        if ($this == $other) {
            return PublicProjectionDeliveryOrderRelation::Equal;
        }

        $direction = [$this->aggregateVersion, $this->eventIndex->value] <=> [$other->aggregateVersion, $other->eventIndex->value];
        $versionDistance = abs($this->aggregateVersion - $other->aggregateVersion);
        $indexDistance = abs($this->eventIndex->value - $other->eventIndex->value);
        $gap = $versionDistance > 1
            || ($versionDistance === 0 && $indexDistance > 1)
            || ($versionDistance === 1 && (($direction > 0 && $this->eventIndex->value !== 1) || ($direction < 0 && $other->eventIndex->value !== 1)));

        if ($gap) {
            return PublicProjectionDeliveryOrderRelation::Gap;
        }

        return $direction < 0 ? PublicProjectionDeliveryOrderRelation::Before : PublicProjectionDeliveryOrderRelation::After;
    }
}
