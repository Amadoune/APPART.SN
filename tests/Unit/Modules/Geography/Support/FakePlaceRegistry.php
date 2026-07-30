<?php

namespace Tests\Unit\Modules\Geography\Support;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Exception\ConcurrentPlaceModification;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceCode;
use Appart\Modules\Geography\Domain\Exception\DuplicatePlaceId;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final class FakePlaceRegistry implements PlaceRegistry
{
    /** @var array<string, Place> */
    private array $places = [];

    public function find(PlaceId $id): ?Place
    {
        return isset($this->places[$id->value]) ? clone $this->places[$id->value] : null;
    }

    public function add(Place $candidate): void
    {
        if (isset($this->places[$candidate->id()->value])) {
            throw DuplicatePlaceId::forId($candidate->id());
        }

        foreach ($this->places as $place) {
            if ($place->countryCode()->value === $candidate->countryCode()->value && $place->code()->value === $candidate->code()->value) {
                throw DuplicatePlaceCode::forCountry($candidate->code(), $candidate->countryCode());
            }
        }

        $stored = clone $candidate;
        $stored->releaseEvents();
        $this->places[$candidate->id()->value] = $stored;
    }

    public function save(Place $place, int $expectedVersion): void
    {
        $stored = $this->places[$place->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentPlaceModification;
        }

        $copy = clone $place;
        $copy->releaseEvents();
        $this->places[$place->id()->value] = $copy;
    }
}
