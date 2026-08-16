<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\RealEstateCatalog\Application\AddressIdentity\AddressIntentId;
use Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence\PropertyAuthoringState;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use UnexpectedValueException;

final readonly class PropertyAuthoringMapper
{
    /** @param array<string, mixed> $row */
    public function toState(array $row): PropertyAuthoringState
    {
        $state = new PropertyAuthoringState(
            (string) $row['property_id'],
            (string) $row['owner_account_id'],
            (int) $row['version'],
            (string) $row['last_intent_id'],
            (string) $row['last_intent_checksum'],
            isset($row['property_type']) ? (string) $row['property_type'] : null,
            isset($row['city']) ? (string) $row['city'] : null,
            isset($row['neighborhood']) ? (string) $row['neighborhood'] : null,
            isset($row['property_reference']) ? (string) $row['property_reference'] : null,
            isset($row['surface_square_meters']) ? (int) $row['surface_square_meters'] : null,
            isset($row['rooms']) ? (int) $row['rooms'] : null,
            isset($row['bathrooms']) ? (int) $row['bathrooms'] : null,
            isset($row['construction_year']) ? (int) $row['construction_year'] : null,
            isset($row['geographic_place_id']) ? (string) $row['geographic_place_id'] : null,
            isset($row['address_line']) ? (string) $row['address_line'] : null,
            isset($row['address_intent_id']) ? (string) $row['address_intent_id'] : null,
        );
        if ($state->version < 1
            || preg_match('/^[0-9a-f]{64}$/', $state->intentChecksum) !== 1
            || ($state->propertyType !== null && PropertyType::tryFrom($state->propertyType) === null)
            || ($state->city !== null && trim($state->city) === '')
            || ($state->neighborhood !== null && trim($state->neighborhood) === '')) {
            throw new UnexpectedValueException('Invalid Property Authoring persistence state.');
        }
        try {
            if ($state->propertyReference !== null && PropertyReference::fromString($state->propertyReference)->value !== $state->propertyReference) {
                throw new UnexpectedValueException('Non-canonical Property reference.');
            }
            $state->surfaceSquareMeters === null || SurfaceArea::fromSquareMeters($state->surfaceSquareMeters);
            $state->rooms === null || RoomCount::fromInt($state->rooms);
            $state->bathrooms === null || BathroomCount::fromInt($state->bathrooms);
            $state->constructionYear === null || ConstructionYear::fromInt($state->constructionYear);
            if ($state->geographicPlaceId !== null && PlaceId::fromString($state->geographicPlaceId)->value !== $state->geographicPlaceId) {
                throw new UnexpectedValueException('Non-canonical Geographic Place id.');
            }
            if ($state->addressLine !== null && AddressLine::fromString($state->addressLine)->value !== $state->addressLine) {
                throw new UnexpectedValueException('Non-canonical Address line.');
            }
            if ($state->addressIntentId !== null && AddressIntentId::fromString($state->addressIntentId)->value !== $state->addressIntentId) {
                throw new UnexpectedValueException('Non-canonical Address intent id.');
            }
        } catch (\Throwable $error) {
            throw new UnexpectedValueException('Invalid Property Authoring source completeness state.', 0, $error);
        }

        return $state;
    }
}
