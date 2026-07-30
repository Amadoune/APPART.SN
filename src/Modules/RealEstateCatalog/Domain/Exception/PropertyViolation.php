<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Exception;

final class PropertyViolation extends RealEstateCatalogException
{
    public static function archived(): self
    {
        return new self('An archived property cannot be modified.');
    }

    public static function alreadyArchived(): self
    {
        return new self('The property is already archived.');
    }

    public static function inconsistentRooms(): self
    {
        return new self('Bathroom count cannot exceed room count.');
    }

    public static function roomsRequired(): self
    {
        return new self('This property type requires at least one room.');
    }

    public static function surfaceRequired(): self
    {
        return new self('This property type requires a surface.');
    }

    public static function addressRequired(): self
    {
        return new self('This property type requires an address.');
    }

    public static function invalidLandDetails(): self
    {
        return new self('Land cannot have rooms, bathrooms, or a construction year.');
    }

    public static function futureConstructionYear(): self
    {
        return new self('Construction year cannot exceed the business year.');
    }

    public static function unchangedAddress(): self
    {
        return new self('The new address is identical to the current address.');
    }

    public static function unchangedDetails(): self
    {
        return new self('The property update contains no change.');
    }
}
