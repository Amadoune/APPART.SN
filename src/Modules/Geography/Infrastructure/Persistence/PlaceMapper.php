<?php

namespace Appart\Modules\Geography\Infrastructure\Persistence;

use Appart\Modules\Geography\Domain\Model\AdministrativeDivision;
use Appart\Modules\Geography\Domain\Model\GeographicAlias;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\Coordinates;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use Throwable;

final class PlaceMapper
{
    public function toSnapshot(Place $place): PlaceSnapshot
    {
        return new PlaceSnapshot(
            $place->id()->value,
            $place->officialName()->value,
            $place->code()->value,
            $place->type()->value,
            $place->countryCode()->value,
            $place->parent()?->placeId->value,
            $place->parent()?->placeType->value,
            $place->coordinates()?->latitude,
            $place->coordinates()?->longitude,
            array_map(static fn (GeographicAlias $alias): PlaceAliasSnapshot => new PlaceAliasSnapshot($alias->name->value, $alias->recordedAt->format('Y-m-d\TH:i:s.uP')), $place->aliases()),
            $place->isEnabled(),
            $place->mergedInto()?->value,
            $place->version(),
        );
    }

    public function toAggregate(PlaceSnapshot $snapshot): Place
    {
        try {
            $type = PlaceType::tryFrom($snapshot->type) ?? throw PersistentPlaceIntegrity::invalid('type');
            $parentType = $snapshot->parentType === null ? null : (PlaceType::tryFrom($snapshot->parentType) ?? throw PersistentPlaceIntegrity::invalid('parent_type'));
            if (($snapshot->parentId === null) !== ($parentType === null) || ($snapshot->latitude === null) !== ($snapshot->longitude === null)) {
                throw PersistentPlaceIntegrity::invalid('nullable_pair');
            }
            $aliases = array_map(fn (PlaceAliasSnapshot $alias): GeographicAlias => new GeographicAlias(PlaceName::fromString($alias->name), $this->date($alias->recordedAt)), $snapshot->aliases);
            $place = Place::reconstitute(
                PlaceId::fromString($snapshot->id),
                PlaceName::fromString($snapshot->officialName),
                PlaceCode::fromString($snapshot->code),
                $type,
                CountryCode::fromString($snapshot->countryCode),
                $snapshot->parentId === null ? null : new AdministrativeDivision(PlaceId::fromString($snapshot->parentId), $parentType),
                $snapshot->latitude === null ? null : Coordinates::fromDecimal($snapshot->latitude, $snapshot->longitude),
                $aliases,
                $snapshot->enabled,
                $snapshot->mergedInto === null ? null : PlaceId::fromString($snapshot->mergedInto),
                $snapshot->version,
            );
            if ($place->releaseEvents() !== []) {
                throw PersistentPlaceIntegrity::invalid('events');
            }

            return $place;
        } catch (PersistentPlaceIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPlaceIntegrity::invalid('snapshot');
        }
    }

    private function date(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d\TH:i:s.uP') !== $value) {
            throw PersistentPlaceIntegrity::invalid('alias_date');
        }

        return $date;
    }
}
