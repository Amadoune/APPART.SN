<?php

namespace Appart\Modules\Geography\Domain\Service;

use Appart\Modules\Geography\Domain\Contract\PlaceLookup;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceHierarchy;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final readonly class PlaceHierarchy
{
    public function __construct(private PlaceLookup $places) {}

    public function verifiedParent(PlaceId $childId, PlaceId $parentId): Place
    {
        if ($childId->equals($parentId)) {
            throw InvalidPlaceHierarchy::selfParenting();
        }

        $parent = $this->places->find($parentId) ?? throw InvalidPlaceHierarchy::parentNotFound($parentId);
        $visited = [$childId->value => true];
        $current = $parent;

        while (true) {
            if (isset($visited[$current->id()->value])) {
                throw InvalidPlaceHierarchy::cycleDetected($current->id());
            }

            $visited[$current->id()->value] = true;
            $ancestor = $current->parent();

            if ($ancestor === null) {
                return $parent;
            }

            $current = $this->places->find($ancestor->placeId)
                ?? throw InvalidPlaceHierarchy::parentNotFound($ancestor->placeId);
        }
    }
}
