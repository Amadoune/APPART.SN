<?php

namespace Appart\Modules\Geography\Domain\Event;

use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;

final readonly class PlaceCreated implements PlaceEvent
{
    public function __construct(
        private PlaceId $placeId,
        public PlaceName $officialName,
        public PlaceCode $officialCode,
        public PlaceType $placeType,
        public CountryCode $countryCode,
        public ?PlaceId $parentPlaceId,
        public ?Coordinates $coordinates,
        private DateTimeImmutable $occurredAt,
        private int $aggregateVersion,
    ) {}

    public function placeId(): PlaceId
    {
        return $this->placeId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
