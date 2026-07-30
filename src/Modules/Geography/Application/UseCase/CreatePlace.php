<?php

namespace Appart\Modules\Geography\Application\UseCase;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\Service\PlaceHierarchy;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;

final readonly class CreatePlace
{
    public function __construct(private PlaceRegistry $registry) {}

    public function execute(
        PlaceId $id,
        PlaceName $officialName,
        PlaceCode $code,
        PlaceType $type,
        CountryCode $countryCode,
        DateTimeImmutable $occurredAt,
        ?PlaceId $parentId = null,
        ?Coordinates $coordinates = null,
    ): Place {
        $parent = $parentId === null ? null : (new PlaceHierarchy($this->registry))->verifiedParent($id, $parentId);
        $place = Place::create($id, $officialName, $code, $type, $countryCode, $occurredAt, $parent, $coordinates);
        $this->registry->add($place);

        return $place;
    }
}
