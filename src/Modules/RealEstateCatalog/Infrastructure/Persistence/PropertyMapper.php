<?php

namespace Appart\Modules\RealEstateCatalog\Infrastructure\Persistence;

use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\Model\Property;
use Appart\Modules\RealEstateCatalog\Domain\Policy\PropertyTypePolicy;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\AddressLine;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BathroomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\BusinessYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\ConstructionYear;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\GeographicPlaceId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyReference;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyStatus;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyType;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\RoomCount;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;
use Throwable;

final class PropertyMapper
{
    public function toSnapshot(Property $property): PropertySnapshot
    {
        $address = $property->address();

        return new PropertySnapshot(
            $property->id()->value,
            $property->reference()->value,
            $property->type()->value,
            $property->surface()?->squareMeters,
            $property->rooms()->value,
            $property->bathrooms()->value,
            $property->constructionYear()?->value,
            $address === null ? null : new AddressSnapshot($address->id->value, $address->placeId->value, $address->line->value),
            $property->status()->value,
            $this->date($property->lastChangedAt()),
            $property->version(),
        );
    }

    public function toAggregate(PropertySnapshot $snapshot): Property
    {
        try {
            $type = PropertyType::tryFrom($snapshot->type) ?? throw PersistentPropertyIntegrity::invalid('type');
            $status = PropertyStatus::tryFrom($snapshot->status) ?? throw PersistentPropertyIntegrity::invalid('status');
            $surface = $snapshot->surface === null ? null : SurfaceArea::fromSquareMeters($snapshot->surface);
            $rooms = RoomCount::fromInt($snapshot->rooms);
            $bathrooms = BathroomCount::fromInt($snapshot->bathrooms);
            $year = $snapshot->constructionYear === null ? null : ConstructionYear::fromInt($snapshot->constructionYear);
            $address = $this->address($snapshot->address);
            $lastChangedAt = $this->parseDate($snapshot->lastChangedAt);
            $validationYear = max((int) $lastChangedAt->format('Y'), $snapshot->constructionYear ?? 0);
            (new PropertyTypePolicy)->assertValid($type, $surface, $rooms, $bathrooms, $year, $address, BusinessYear::fromInt($validationYear));

            $property = Property::reconstitute(
                PropertyId::fromString($snapshot->id),
                PropertyReference::fromString($snapshot->reference),
                $type,
                $surface,
                $rooms,
                $bathrooms,
                $year,
                $address,
                $status,
                $lastChangedAt,
                $snapshot->version,
            );
            if ($property->releaseEvents() !== []) {
                throw PersistentPropertyIntegrity::invalid('events');
            }

            return $property;
        } catch (PersistentPropertyIntegrity $error) {
            throw $error;
        } catch (Throwable) {
            throw PersistentPropertyIntegrity::invalid('snapshot');
        }
    }

    private function address(?AddressSnapshot $snapshot): ?Address
    {
        return $snapshot === null ? null : new Address(
            AddressId::fromString($snapshot->id),
            GeographicPlaceId::fromString($snapshot->placeId),
            AddressLine::fromString($snapshot->line),
        );
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }

    private function parseDate(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $date);
        $errors = DateTimeImmutable::getLastErrors();
        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $this->date($parsed) !== $date) {
            throw PersistentPropertyIntegrity::invalid('date');
        }

        return $parsed;
    }
}
