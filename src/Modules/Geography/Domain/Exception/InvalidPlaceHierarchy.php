<?php

namespace Appart\Modules\Geography\Domain\Exception;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;

final class InvalidPlaceHierarchy extends GeographyException
{
    public static function countryCannotHaveParent(): self
    {
        return new self('A country cannot have a parent place.');
    }

    public static function parentIsRequired(PlaceType $type): self
    {
        return new self(sprintf('A place of type "%s" requires a parent.', $type->value));
    }

    public static function incompatible(PlaceType $type, PlaceType $parentType): self
    {
        return new self(sprintf('A place of type "%s" cannot have a parent of type "%s".', $type->value, $parentType->value));
    }

    public static function selfParenting(): self
    {
        return new self('A place cannot be its own parent.');
    }

    public static function parentNotFound(PlaceId $id): self
    {
        return new self(sprintf('Parent place "%s" does not exist.', $id->value));
    }

    public static function parentDisabled(): self
    {
        return new self('A place cannot be attached to a disabled parent.');
    }

    public static function parentMerged(): self
    {
        return new self('A place cannot be attached to a merged parent.');
    }

    public static function differentCountry(): self
    {
        return new self('A place and its parent must belong to the same country.');
    }

    public static function cycleDetected(PlaceId $id): self
    {
        return new self(sprintf('The hierarchy contains a cycle through place "%s".', $id->value));
    }
}
