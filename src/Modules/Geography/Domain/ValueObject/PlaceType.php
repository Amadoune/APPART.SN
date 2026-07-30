<?php

namespace Appart\Modules\Geography\Domain\ValueObject;

enum PlaceType: string
{
    case Country = 'country';
    case Region = 'region';
    case Department = 'department';
    case City = 'city';
    case District = 'district';
    case Neighborhood = 'neighborhood';

    public function acceptsParent(self $parent): bool
    {
        return match ($this) {
            self::Country => false,
            self::Region => $parent === self::Country,
            self::Department => $parent === self::Region,
            self::City => in_array($parent, [self::Region, self::Department], true),
            self::District => in_array($parent, [self::Department, self::City], true),
            self::Neighborhood => in_array($parent, [self::City, self::District], true),
        };
    }
}
