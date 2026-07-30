<?php

namespace Appart\Modules\Geography\Application\Contract;

use Appart\Modules\Geography\Domain\Contract\PlaceLookup;
use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceCode;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceId;
use Appart\Modules\Geography\Domain\Model\Place;

interface PlaceRegistry extends PlaceLookup
{
    /**
     * Atomically adds a new place.
     *
     * @throws DuplicatePlaceId
     * @throws DuplicatePlaceCode
     */
    public function add(Place $place): void;

    /**
     * Saves a detached aggregate only when its persisted version equals the expected version.
     *
     * @throws ConcurrentPlaceModification
     */
    public function save(Place $place, int $expectedVersion): void;
}
