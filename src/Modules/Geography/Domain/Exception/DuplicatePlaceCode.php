<?php

namespace Appart\Modules\Geography\Domain\Exception;

use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceCode;

final class DuplicatePlaceCode extends GeographyException
{
    public static function forCountry(PlaceCode $code, CountryCode $countryCode): self
    {
        return new self(sprintf('Official place code "%s" already exists in country "%s".', $code->value, $countryCode->value));
    }
}
